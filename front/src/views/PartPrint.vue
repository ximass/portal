<template>
  <v-container class="pa-8" fluid>
    <v-row justify="center">
      <v-col cols="12" md="6">
        <v-card class="text-center pa-8">
          <v-icon size="64" color="primary" class="mb-4">mdi-file-pdf-box</v-icon>
          <v-card-title class="text-h5 mb-2">Gerando PDF de impressão</v-card-title>
          <v-card-text>
            <p class="text-body-1 mb-4">
              O PDF das peças está sendo preparado para impressão.
            </p>
            <v-progress-circular
              indeterminate
              color="primary"
              size="48"
            ></v-progress-circular>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default defineComponent({
  props: {
    order_id: { type: [String, Number], default: null },
  },
  setup(props) {
    const route = useRoute();
    const router = useRouter();

    onMounted(() => {
      const baseUrl = import.meta.env.VITE_API_URL;
      let pdfUrl = '';

      if (props.order_id) {
        pdfUrl = `${baseUrl}/api/orders/${props.order_id}/pdf-parts`;
      } else if (route.params.id) {
        pdfUrl = `${baseUrl}/api/set-parts/${route.params.id}/pdf`;
      }

      if (pdfUrl) {
        window.open(pdfUrl, '_blank');
        
        setTimeout(() => {
          router.back();
        }, 1000);
      } else {
        router.back();
      }
    });

    return {};
  },
});
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}
</style>
