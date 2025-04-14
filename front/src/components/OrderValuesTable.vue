<template>
  <v-card>
    <v-card-title>Valores</v-card-title>
    <v-card-text>
      <v-data-table
        :headers="headers"
        :items="items"
        :group-by="groupBy"
        class="elevation-1"
        hide-default-footer
        density="compact"
      >
        <template v-slot:group-header="{ item, columns, toggleGroup, isGroupOpen }">
          <tr>
            <td :colspan="columns.length">
              <div class="d-flex align-center">
                <v-btn
                  :icon="isGroupOpen(item) ? '$expand' : '$next'"
                  color="medium-emphasis"
                  density="comfortable"
                  size="small"
                  variant="outlined"
                  @click="toggleGroup(item)"
                ></v-btn>
                <span class="ms-4">{{ item.value }}</span>
              </div>
            </td>
          </tr>
        </template>
        <template #item.unit_value="{ item }">
          R$ {{ item.unit_value ?? 0 }}
        </template>
        <template #item.final_value="{ item }">
          R$ {{ item.unit_value * item.quantity }}
        </template>
        <template #item.net_weight="{ item }">
          {{ item.unit_net_weight * item.quantity }}
        </template>
        <template #item.gross_weight="{ item }">
          {{ item.unit_gross_weight * item.quantity }}
        </template>
        <template #body.append>
          <tr>
            <td colspan="2" class="text-right font-weight-bold">Total:</td>
            <td class="font-weight-bold">
              {{ computedTotalValue(items, part => part.unit_value) }}
            </td>
            <td class="font-weight-bold">
              {{ computedTotalWeight(items, part => part.quantity) }}
            </td>
            <td class="font-weight-bold">
              {{ computedTotalValue(items, part => part.unit_value * part.quantity) }}
            </td>
            <td class="font-weight-bold">
              {{ computedTotalWeight(items, part => part.unit_gross_weight * part.quantity) }}
            </td>
            <td class="font-weight-bold">
              {{ computedTotalWeight(items, part => part.unit_net_weight * part.quantity) }}
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Part } from '@/types/types';

export default defineComponent({
  name: 'OrderValuesTable',
  props: {
    headers: {
      type: Array,
      required: true
    },
    items: {
      type: Array as () => Part[],
      required: true
    },
    groupBy: {
      type: Array,
      required: true
    }
  },
  methods: {
    computedTotalValue(items: Part[], fn: (item: any) => number): string {
        const total = items.reduce((acc, item) => {
          const number = Number(fn(item));

          return acc + (isNaN(number) ? 0 : number);
        }, 0);

        return total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    computedTotalWeight(items: Part[], fn: (item: any) => number): string {
        const total = items.reduce((acc, item) => {
          const number = Number(fn(item));

          return acc + (isNaN(number) ? 0 : number);
        }, 0);

        return total.toFixed(2);
    }
  }
});
</script>
