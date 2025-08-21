# Regras de Cálculo — SetPartController

Este documento descreve, em português, as regras de cálculo implementadas em `App\Http\Controllers\SetPartController` (arquivo: `api/app/Http/Controllers/SetPartController.php`). Ele resume o comportamento de cada função de cálculo e mostra entradas/saídas, locks, ordem de operações, edge cases e um exemplo numérico.

## Visão geral

A função principal é `calculateProperties(object $part): object` que calcula e preenche os campos de pesos, valores e impostos para um objeto `part` (representando um `SetPart` em memória). Funções auxiliares importantes:

- `calculateSheetProperties` (usada também por `calculateMaterialProperties`)
- `calculateBarProperties`
- `calculateComponentProperties`
- `calculatePartProcesses`
- `calculateIpiValues`
- `calculateIcmsValues`
- `applyIpiToUnitValue`

Campos calculados retornados no `part`:
- `unit_net_weight`, `net_weight`
- `unit_gross_weight`, `gross_weight`
- `unit_value`, `final_value`
- `unit_ipi_value`, `total_ipi_value`
- `unit_icms_value`, `total_icms_value`

## Contrato (inputs / outputs / erros)

- Input: `part` (objeto) com campos variando por tipo. Campos relevantes:
  - `type` (string): `material|sheet|bar|component|process`
  - `quantity`, `width`, `length`, `thickness`, `loss`, `locked_values` (array)
  - IDs conforme o tipo: `material_id`, `sheet_id`, `bar_id`, `component_id`, `ncm_id`
  - `set` (opcional) contendo `order.markup` e `order.customer.state`
  - `markup` (opcional) sobrescreve `set->order->markup`

- Output: o mesmo objeto `part` com campos calculados descritos acima.

- Erros: `findOrFail()` nas models lançará `ModelNotFoundException` se IDs inválidos; `calculateProperties` lança `InvalidArgumentException` para `type` inválido.

## Comportamento inicial

- Se `quantity` estiver ausente ou for zero: todos os pesos, valores e impostos são forçados a 0 e o método retorna imediatamente.

## Locked values (`locked_values`)

- `locked_values` é um array de strings que indica campos que não devem ser sobrescritos.
- Regras principais:
  - Se um `unit_*` estiver travado, o cálculo usa o `unit_*` informado e atualiza o total como `unit * quantity`.
  - Se um `*_weight` total estiver travado, o `unit_*` é recalculado como `total / quantity` (quando `quantity > 0`).
  - Se `unit_value` estiver travado, não é recalculado; se `final_value` estiver travado, também é respeitado.

## Conversões e constantes

- Unidades:
  - width, length, thickness no `part` são esperados em mm.
  - Converte-se para metros: `m = mm / 1000`.
- `specificWeight` do material é multiplicado no código por 1_000_000 (`$material->specific_weight * 1000 * 1000`) — ver observação sobre unidades abaixo.
- `loss` é arredondado para 2 casas; fator aplicado: `factor = (100 - loss) / 100`.
- `markup` = `part->markup` se presente, senão `part->set->order->markup`, senão `1`.

## Cálculos por tipo

### Tipo `material`
- Alias para `calculateSheetProperties($part, true)`.
- Busca `Material::findOrFail($part->material_id)` e espera que `thickness` esteja presente no `part`.
- Aplica o markup conforme descrito abaixo.

### Tipo `sheet` (e `material` via alias)
1. Obtém `sheet` (quando aplicável) e `material` associado. Obtém `thickness` do `sheet` ou do `part` (para `material`).
2. Converte medidas: `width_m = width / 1000`, `length_m = length / 1000`, `thickness_m = thickness / 1000`.
3. `specificWeight = material->specific_weight * 1_000_000`.
4. Pesos:
   - `unitNetWeight = width_m * length_m * thickness_m * specificWeight * factor`
   - `unitGrossWeight = width_m * length_m * thickness_m * specificWeight`
   - `netWeight = unitNetWeight * quantity`
   - `grossWeight = unitGrossWeight * quantity`
5. Valores:
   - `unitValue = unitGrossWeight * material->price_kg * markup`
   - `finalValue = unitValue * quantity`
6. Aplica `locked_values` conforme regras gerais.

Observação: o código usa `unitGrossWeight` (não net) para calcular o preço por kg.

### Tipo `bar`
1. Busca `Bar::findOrFail($part->bar_id)`.
2. `proportion = bar->length > 0 ? part->length / bar->length : 0`.
3. `partialWeight = bar->weight * proportion`.
4. `unitNetWeight = partialWeight * factor` e `unitGrossWeight = partialWeight`.
5. `unitValue = unitGrossWeight * bar->price_kg * markup`.
6. Regras de `locked_values` aplicadas a pesos e valores.

### Tipo `component`
1. Busca `Component::findOrFail($part->component_id)`.
2. Por padrão os pesos são zero (`unit_*` e totals = 0).
3. `unitValue = component->unit_value * markup` e `finalValue = unitValue * quantity`.
4. `locked_values` pode sobrescrever `unit_value`/`final_value` ou pesos.

### Tipo `process`
- Zera pesos e valores base (`unit_value`, `final_value`, `unit_ipi_value`, `total_ipi_value`, `unit_icms_value`, `total_icms_value` = 0).
- Não aplica markup individual (apenas o markup dos processos associados).
- Ainda assim pode acumular valores via `calculatePartProcesses`.

## Processos associados (`calculatePartProcesses`)

- Para cada processo em `part->processes` (cada item: `{ id, pivot: { time } }`):
  - Carrega `Process` e chama `ProcessController::calculateValue($process, ['time' => time])` para obter o valor unitário do processo.
  - Soma `processUnit` (unitário) e `processFinal` (`unit * quantity`).
- Se `unit_value` não estiver travado, `unit_value = baseUnitValue + processUnit`.
- Se `final_value` não estiver travado, `final_value = baseFinalValue + processFinal`.

## IPI (`calculateIpiValues`)

- Só é aplicado se `ncm_id` existir e `unit_value` for > 0.
- Busca `MercosurCommonNomenclature::findOrFail(ncm_id)` e usa `ncm->ipi`.
- Cálculo:
  - `unit_ipi_value = round(unit_value * (ncm->ipi / 100), 2)`
  - `total_ipi_value = unit_ipi_value * quantity`
- Em caso de erro, IPI é zerado.

## Aplicação do IPI ao valor unitário (`applyIpiToUnitValue`)

- Se `ncm_id` presente e `unit_value` presente:
  - Se `unit_value` não estiver travado, `unit_value += unit_ipi_value`.
  - Se `final_value` não estiver travado, `final_value = unit_value * quantity`.

Efeito: o IPI é somado ao `unit_value` (a menos que travado), e `final_value` é recalculado.

## ICMS (`calculateIcmsValues`)

- Só é aplicado se `unit_value` existir e for > 0.
- O estado (`state`) é obtido preferencialmente de `part->set->order->customer->state`.
- Fallback: tenta carregar `SetPart::with('set.order.customer.state')->find($part->id)`.
- Se `state->icms > 0`:
  - `unit_icms_value = round(unit_value * (state->icms / 100), 2)`
  - `total_icms_value = unit_icms_value * quantity`
- Em caso de erro, ICMS é zerado.

Observação: a implementação atual calcula o ICMS antes de `applyIpiToUnitValue`, portanto o ICMS incide sobre o `unit_value` antes do acréscimo do `unit_ipi_value`. Se for necessário que ICMS incida sobre o valor já com IPI, a ordem de chamadas deve ser ajustada.

## Ordem de execução dos cálculos

1. Se `quantity` == 0 → zera tudo e retorna.
2. Calcula propriedades base por tipo (`material/sheet/bar/component/process`).
3. `calculatePartProcesses` — soma valores de processos.
4. `calculateIpiValues`.
5. `calculateIcmsValues`.
6. `applyIpiToUnitValue`.

Importante: por essa ordem, o ICMS é calculado antes do ajuste final do `unit_value` com IPI.

## Edge cases e notas

- `quantity = 0` ou ausente: todos os valores zerados.
- IDs inválidos (`findOrFail`) resultarão em exceção.
- `bar->length = 0` → `proportion = 0` evita divisão por zero.
- `locked_values` ausente é tratado como array vazio.
- `markup` default é `1` se não houver valor no `part` nem no `order`.
- Verificar se `material->specific_weight`, `price_kg`, `bar->weight`, `component->unit_value` existem/estão validados.

## Exemplo numérico (sheet) — ilustração rápida

Dados de exemplo (ilustrativo):
- width = 1000 mm (1 m)
- length = 2000 mm (2 m)
- thickness = 10 mm (0.01 m)
- material->specific_weight = 7.85 (unidade do modelo — confirmar)
- material->price_kg = 10 (R$/kg)
- loss = 5 (%)
- quantity = 2
- markup = 1.2

Seguindo as fórmulas do código (atenção às unidades do `specific_weight`):
- width_m = 1.0, length_m = 2.0, thickness_m = 0.01
- specificWeight (no código) = 7.85 * 1_000_000 = 7_850_000
- factor = 0.95
- unitNetWeight = 1.0 * 2.0 * 0.01 * 7_850_000 * 0.95 = 149150
- unitGrossWeight = 157000
- netWeight = 298300
- unitValue = 157000 * 10 * 1.2 = 1.884.000
- finalValue = 3.768.000

Observação: os valores acima demonstram a importância de confirmar as unidades armazenadas em `specific_weight`. Se o domínio usar kg/m³, a multiplicação por 1e6 no código pode estar incorreta e deve ser ajustada.

## Recomendações

- Documentar as unidades exatas de `Material::specific_weight` no modelo (kg/m³, g/mm³, etc.) e ajustar a conversão se necessário.
- Rever se o ICMS deve incidir sobre o valor já com IPI; se sim, mover `calculateIcmsValues` para depois de `applyIpiToUnitValue`.
- Adicionar validações ou defaults para `price_kg`, `specific_weight`, `bar->weight`, `component->unit_value` para reduzir exceções inesperadas.

---

Arquivo fonte referenciado: `api/app/Http/Controllers/SetPartController.php`

Criado: `api/docs/set-part-calculations.md` — documentação das regras de cálculo.
