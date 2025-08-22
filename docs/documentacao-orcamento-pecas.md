# Documentação: Orçamento por Peças Detalhadas

## Como são calculados os valores no orçamento por peças

### 1. Valor Unitário da Peça

O valor unitário de cada peça é calculado da seguinte forma:

- **Valor com IPI**: O sistema primeiro obtém o valor da peça que já inclui o IPI
- **Remoção do IPI**: Para mostrar o valor "limpo", o sistema calcula:
  ```
  Valor Unitário (sem IPI) = Valor com IPI ÷ (1 + IPI)
  ```
  
**Exemplo prático:**
- Peça com valor final de R$ 126,00 e IPI de 5%
- Valor unitário mostrado: R$ 126,00 ÷ 1,05 = R$ 120,00

### 2. Cálculo do Valor Base das Peças por Tipo

O sistema calcula automaticamente os valores base dependendo do tipo de material:

#### **Material/Chapa:**
- Considera: largura, comprimento, espessura, peso específico do material
- Aplica perda de material (se houver)
- Multiplica pelo preço por kg do material
- Aplica markup (margem de lucro)

#### **Barra:**
- Calcula proporção do comprimento utilizado da barra
- Considera o peso da barra e preço por kg
- Aplica perda e markup

#### **Componente:**
- Usa valor unitário direto do componente
- Aplica apenas o markup

#### **Processo:**
- Calcula com base no tempo de processo
- Multiplica pelo valor por minuto

### 3. IPI (Imposto sobre Produtos Industrializados)

- O IPI é definido no cadastro do NCM de cada peça
- É exibido como porcentagem (ex: 5,00%)
- **Importante**: O valor unitário mostrado no orçamento está SEM o IPI

### 4. ICMS (Imposto sobre Circulação de Mercadorias e Serviços)

- O ICMS é definido pelo estado do cliente
- É exibido como porcentagem (ex: 18,00%)
- Varia conforme a localização do cliente

### 5. Valor Total da Peça

O valor total é calculado como:
```
Valor Total = Valor Unitário × Quantidade da Peça
```

**Exemplo:**
- Valor unitário: R$ 50,00
- Quantidade: 4 peças
- Valor total: R$ 50,00 × 4 = R$ 200,00

### 6. Total Geral do Orçamento

O total geral soma:
```
Total Geral = Soma de todos os valores totais das peças + Valor do Frete
```

### 7. Informações por Peça

Cada peça contém:
- **Imagem**: Foto ou desenho técnico da peça
- **Quantidade**: Número de unidades
- **Referência EngFrig**: Código interno da empresa
- **Referência Cliente**: Código fornecido pelo cliente

### 8. Agrupamento por Conjunto

- As peças são organizadas por conjunto
- Cada conjunto pode ter várias peças

### 9. Valores Bloqueados

O sistema permite "travar" alguns valores para não serem recalculados automaticamente:
- Peso unitário
- Peso total  
- Valor unitário
- Valor final

Isso é útil quando há acordos específicos ou preços negociados.

---
