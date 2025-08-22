# Documentação: Orçamento por Conjuntos

## Como são calculados os valores no orçamento por conjuntos

### 1. Valor Unitário do Conjunto

O valor unitário de cada conjunto é calculado da seguinte forma:

- **Soma todas as peças do conjunto**: Primeiro, o sistema pega todas as peças que fazem parte do conjunto
- **Remove o IPI de cada peça**: Para cada peça, o sistema calcula o valor base (sem IPI) usando a fórmula:
  ```
  Valor Base da Peça = Valor Final da Peça ÷ (1 + IPI)
  ```
  Por exemplo: Se uma peça tem valor final de R$ 105,00 e IPI de 5%, o valor base será:
  R$ 105,00 ÷ 1,05 = R$ 100,00

- **Soma os valores base**: O valor unitário do conjunto é a soma de todos os valores base das peças

### 2. IPI (Imposto sobre Produtos Industrializados)

- O IPI é definido no cadastro do NCM (Nomenclatura Comum do Mercosul) de cada conjunto
- É exibido como porcentagem (ex: 5,00%)
- **Importante**: O valor unitário mostrado no orçamento já está SEM o IPI

### 3. ICMS (Imposto sobre Circulação de Mercadorias e Serviços)

- O ICMS é definido pelo estado do cliente
- É exibido como porcentagem (ex: 18,00%)
- Cada estado tem sua própria alíquota de ICMS

### 4. Valor Total do Conjunto

O valor total é calculado como:
```
Valor Total = Valor Unitário × Quantidade do Conjunto
```

**Exemplo prático:**
- Conjunto com 3 peças: R$ 50,00 + R$ 30,00 + R$ 20,00 = R$ 100,00 (valor unitário)
- Quantidade do conjunto: 2 unidades  
- Valor total: R$ 100,00 × 2 = R$ 200,00

### 5. Total Geral do Orçamento

O total geral é calculado como:
```
Total Geral = Soma de todos os valores totais dos conjuntos + Valor do Frete
```

### 6. Informações Complementares

- **Referência EngFrig**: Código interno da empresa para identificação do produto
- **Referência Cliente**: Código fornecido pelo cliente para sua própria identificação

---
