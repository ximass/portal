# Resumo das Regras de Cálculo

Este documento apresenta, em linguagem simples, como calculamos pesos, valores e impostos das peças/itens no sistema. O objetivo é explicar de forma clara o que consideramos nos cálculos, sem termos técnicos ou nomes de funções.

## O que o sistema usa como entrada

Para calcular cada peça, usamos os dados fornecidos no pedido, por exemplo:
- Tipo da peça: material (chapas), barra, componente ou serviço/processo.
- Quantidade (número de unidades).
- Dimensões quando aplicáveis: largura, comprimento e espessura (medidas em milímetros).
- Perda estimada em porcentagem (ex.: 5%).
- Valores do material (peso específico e preço por kg) ou preço do componente/barra.
- Markup específico da peça (opcional) - permite ajustar o preço individualmente para cada peça.
- Se a peça tiver operações adicionais (processos), informamos o tempo para cada processo.
- Campos que você pode bloquear manualmente (por ex., valor final ou peso) para impedir que o sistema os recalule.

## Principais cálculos (visão geral)

1. Peso unitário: calculamos o peso de uma unidade a partir das dimensões e das propriedades do material (ou a partir da parcela da barra quando se trata de barra). Aplicamos a perda informada para obter o peso líquido.

2. Peso total: multiplicamos o peso unitário pela quantidade.

3. Valor unitário: calculamos o preço por unidade com base no peso (quando aplicável) ou usamos o preço direto do componente/barra. Aplicamos o markup do pedido quando houver.

4. Valor total: multiplicamos o valor unitário pela quantidade.

5. Custos de processos: se houver operações (ex.: usinagem), somamos o custo dessas operações ao valor unitário e ao valor total.

6. IPI (imposto sobre produtos industrializados): quando aplicável ao produto (de acordo com a classificação fiscal informada), calculamos o IPI por unidade e o total.

7. ICMS (imposto sobre circulação): calculamos o ICMS por unidade e o total com base na alíquota do estado do cliente.

8. Ajustes por campos bloqueados: se você travar manualmente o peso unitário, peso total, valor unitário ou valor total, o sistema respeita esses valores e ajusta os demais campos de acordo.

## Observações importantes (pontos que merecem atenção)

- Unidades de medida: as dimensões devem ser informadas em milímetros (mm). O sistema converte para metros internamente para calcular volumes e pesos.

- Perda: a perda é informada em porcentagem e reduz o peso líquido final (por exemplo, perda de 5% significa que consideramos 95% do material teoricamente utilizado).

- Markup: o valor final por unidade pode ser ajustado por dois tipos de multiplicadores:
  1. **Markup específico da peça**: definido individualmente para cada peça (prioritário)
  2. **Markup geral do pedido**: aplicado a todas as peças do pedido (usado quando não há markup específico)
  O sistema sempre usa o markup específico da peça quando disponível, senão usa o markup geral do pedido. Peças do tipo "processo" não aplicam markup individual.

- Ordem dos cálculos e impostos: o sistema soma primeiro os custos de material/peças e processos, calcula o IPI aplicável e também calcula o ICMS com base no valor vigente. Observação: atualmente o cálculo do ICMS é feito sobre o preço base antes de somar o IPI em alguns casos; se você preferir que o ICMS incida sobre o valor já acrescido do IPI, podemos ajustar essa regra mediante solicitação.

- Campos bloqueados: quando um campo é bloqueado, assumimos o valor informado e recalculamos os demais campos para manter a consistência. Por exemplo, se você bloquear o valor final, o sistema manterá esse valor e ajustará o valor unitário se necessário.

## Exemplo simples (ilustrativo)

Suponha uma chapa com as seguintes informações:
- Largura: 1000 mm (1,0 m)
- Comprimento: 2000 mm (2,0 m)
- Espessura: 10 mm (0,01 m)
- Quantidade: 2
- Perda: 5%
- Preço do material por kg: R$ 10,00
- Markup do pedido: 1,2 (20% de margem)

Fluxo simplificado dos resultados que o sistema produziria:
- Calcula o volume da peça e converte em peso (peso unitário).
- Aplica a perda para obter o peso líquido por unidade.
- Calcula o valor unitário a partir do peso e do preço por kg, aplicando o markup.
- Calcula o valor total multiplicando por 2 (quantidade).
- Soma custos de processos (se houver).
- Calcula IPI e ICMS conforme aplicável.

> Nota: os números finais dependem da unidade e do fator de conversão usados internamente; este exemplo apenas ilustra a sequência e o propósito dos cálculos.

---

Arquivo de referência: `api/app/Http/Controllers/SetPartController.php`
Documento criado em: `api/docs/set-part-calculations-client.md`
