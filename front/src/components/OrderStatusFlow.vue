<template>
  <v-card class="mb-4" elevation="2">
    <v-card-title class="d-flex align-center">
      <v-icon class="mr-2" color="secundary">mdi-timeline-check</v-icon>
      Status do {{ orderType === 'pre_order' ? 'orçamento' : 'pedido' }}
    </v-card-title>
    <v-card-text>
      <v-stepper 
        :model-value="currentStepIndex + 1" 
        alt-labels
        hide-actions
        class="elevation-0"
        :color="getCurrentColor()"
      >
        <v-stepper-header>
          <template v-for="(step, index) in statusSteps" :key="step.value">
            <v-stepper-item
              :value="index + 1"
              :title="step.title"
              :subtitle="step.subtitle"
              :complete="currentStepIndex > index"
              :color="getStepColor(index)"
              :icon="getStepIcon(index)"
            />
            <v-divider v-if="index < statusSteps.length - 1" />
          </template>
        </v-stepper-header>
      </v-stepper>

      <!-- Ações de transição -->
      <v-divider class="my-4" />
      
      <div class="d-flex flex-column gap-3">
        <div>
          <p class="text-subtitle-2 mb-2">Status atual: <strong>{{ currentStatusLabel }}</strong></p>
        </div>

        <div v-if="availableTransitions.length > 0" class="d-flex flex-wrap gap-2">
          <v-btn
            v-for="transition in availableTransitions"
            :key="transition.value"
            :color="transition.color"
            :variant="transition.variant || 'elevated'"
            :prepend-icon="transition.icon"
            @click="confirmTransition(transition)"
            :loading="loading"
          >
            {{ transition.label }}
          </v-btn>
        </div>

        <v-alert
          v-else-if="currentStatus === 'finished'"
          type="success"
          variant="tonal"
          density="compact"
        >
          Pedido finalizado
        </v-alert>
      </div>
    </v-card-text>

    <!-- Dialog de confirmação -->
    <v-dialog v-model="showConfirmDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h6">
          Confirmar mudança de status
        </v-card-title>
        <v-card-text>
          <p class="mb-2">
            Você está prestes a alterar o status de 
            <strong>{{ currentStatusLabel }}</strong> para 
            <strong>{{ getStatusLabel(pendingTransition?.value) }}</strong>.
          </p>
          <v-alert
            v-if="pendingTransition?.value === 'approved'"
            type="info"
            variant="tonal"
            density="compact"
            class="mt-3"
          >
            Ao aprovar este orçamento, ele será convertido automaticamente em um pedido.
          </v-alert>
          <v-alert
            v-if="pendingTransition?.value === 'cancelled'"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-3"
          >
            Atenção: Esta ação cancelará o orçamento.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="showConfirmDialog = false">
            Cancelar
          </v-btn>
          <v-btn
            :color="pendingTransition?.color"
            variant="elevated"
            @click="executeTransition"
            :loading="loading"
          >
            Confirmar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script lang="ts">
import { defineComponent, computed, ref, type PropType } from 'vue';
import type { OrderType, AllOrderStatus } from '../types/types';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useAuth } from '../composables/useAuth';

interface StatusTransition {
  label: string;
  value: AllOrderStatus;
  color: string;
  icon: string;
  variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain';
}

interface StatusStep {
  title: string;
  subtitle: string;
  value: AllOrderStatus;
}

export default defineComponent({
  name: 'OrderStatusFlow',
  props: {
    orderId: {
      type: Number,
      required: true,
    },
    orderType: {
      type: String as PropType<OrderType>,
      required: true,
    },
    currentStatus: {
      type: String as PropType<AllOrderStatus>,
      required: true,
    },
  },
  emits: ['status-updated'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const { hasPermission } = useAuth();
    const loading = ref(false);
    const showConfirmDialog = ref(false);
    const pendingTransition = ref<StatusTransition | null>(null);

    const preOrderSteps: StatusStep[] = [
        { title: 'Em andamento', subtitle: 'Orçamento sendo elaborado', value: 'in_progress' },
        { title: 'Aguardando resposta', subtitle: 'Enviado ao cliente', value: 'waiting_response' },
        { title: 'Cancelado', subtitle: 'Orçamento cancelado', value: 'cancelled' },
        { title: 'Aprovado', subtitle: 'Orçamento aprovado', value: 'approved' },
    ];

    const orderSteps: StatusStep[] = [
        { title: 'Aguardando liberação', subtitle: 'Pedido confirmado', value: 'waiting_release' },
        { title: 'Liberado para produção', subtitle: 'Em produção', value: 'released_for_production' },
        { title: 'Finalizado', subtitle: 'Pedido completo', value: 'finished' },
    ];

    const statusSteps = computed(() => {
      return props.orderType === 'pre_order' ? preOrderSteps : orderSteps;
    });

    const currentStepIndex = computed(() => {
      return statusSteps.value.findIndex(step => step.value === props.currentStatus);
    });

    const currentStatusLabel = computed(() => {
      const step = statusSteps.value.find(s => s.value === props.currentStatus);
      return step ? step.title : props.currentStatus;
    });

    const getStepColor = (stepIndex: number) => {
      if (stepIndex < currentStepIndex.value) return 'success';
      if (stepIndex === currentStepIndex.value) {
        if (props.currentStatus === 'cancelled') return 'error';
        return 'primary';
      }
      return 'grey';
    };

    const getStepIcon = (stepIndex: number) => {
      if (stepIndex < currentStepIndex.value) return 'mdi-check';
      if (stepIndex === currentStepIndex.value) {
        if (props.currentStatus === 'cancelled') return 'mdi-close-circle';
        return 'mdi-clock-outline';
      }
      return undefined;
    };

    const getCurrentColor = () => {
      if (props.currentStatus === 'cancelled') return 'error';
      if (props.currentStatus === 'finished') return 'success';
      return 'primary';
    };

    // Mapa de transições possíveis
    const transitionsMap: Record<AllOrderStatus, StatusTransition[]> = {
      in_progress: [
        { label: 'Próximo', value: 'waiting_response', color: 'primary', icon: 'mdi-arrow-right' },
        { label: 'Cancelar', value: 'cancelled', color: 'error', icon: 'mdi-close-circle', variant: 'outlined' },
        { label: 'Aprovar', value: 'approved', color: 'success', icon: 'mdi-check-circle' },
      ],
      waiting_response: [
        { label: 'Anterior', value: 'in_progress', color: 'secondary', icon: 'mdi-arrow-left', variant: 'outlined' },
        { label: 'Cancelar', value: 'cancelled', color: 'error', icon: 'mdi-close-circle', variant: 'outlined' },
        { label: 'Aprovar', value: 'approved', color: 'success', icon: 'mdi-check-circle' },
      ],
      approved: [],
      cancelled: [
        { label: 'Voltar', value: 'waiting_response', color: 'secondary', icon: 'mdi-arrow-left', variant: 'outlined' },
      ],
      waiting_release: [
        { label: 'Próximo', value: 'released_for_production', color: 'primary', icon: 'mdi-arrow-right' },
      ],
      released_for_production: [
        { label: 'Anterior', value: 'waiting_release', color: 'secondary', icon: 'mdi-arrow-left', variant: 'outlined' },
        { label: 'Finalizar', value: 'finished', color: 'success', icon: 'mdi-check-circle' },
      ],
      finished: [
        { label: 'Anterior', value: 'released_for_production', color: 'secondary', icon: 'mdi-arrow-left', variant: 'outlined' },
      ],
    };

    const availableTransitions = computed(() => {
      const transitions = transitionsMap[props.currentStatus] || [];
      
      return transitions.filter(transition => {
        if (transition.value === 'approved') {
          return hasPermission('approve_pre_orders');
        }
        return true;
      });
    });

    const confirmTransition = (transition: StatusTransition) => {
      // Executa imediatamente para ações de navegação (Anterior/Próximo)
      if (transition.label === 'Anterior' || transition.label === 'Próximo') {
        executeTransitionDirect(transition);
      } else {
        // Mostra confirmação para outras ações (Cancelar, Aprovar, Finalizar)
        pendingTransition.value = transition;
        showConfirmDialog.value = true;
      }
    };

    const executeTransitionDirect = async (transition: StatusTransition) => {
      loading.value = true;
      try {
        const response = await axios.put(`/api/orders/${props.orderId}/update-status`, {
          status: transition.value,
        });

        showToast('Status atualizado com sucesso', 'success');
        emit('status-updated', response.data);
      } catch (error: any) {
        const message = error.response?.data?.message || 'Erro ao atualizar status';
        showToast(message, 'error');
      } finally {
        loading.value = false;
      }
    };

    const executeTransition = async () => {
      if (!pendingTransition.value) return;

      loading.value = true;
      try {
        const response = await axios.put(`/api/orders/${props.orderId}/update-status`, {
          status: pendingTransition.value.value,
        });

        showToast('Status atualizado com sucesso', 'success');
        emit('status-updated', response.data);
        showConfirmDialog.value = false;
      } catch (error: any) {
        const message = error.response?.data?.message || 'Erro ao atualizar status';
        showToast(message, 'error');
      } finally {
        loading.value = false;
        pendingTransition.value = null;
      }
    };

    const getStatusLabel = (statusValue: AllOrderStatus | undefined) => {
      if (!statusValue) return '';
      const allSteps = [...preOrderSteps, ...orderSteps];
      const step = allSteps.find(s => s.value === statusValue);
      return step ? step.title : statusValue;
    };

    return {
      loading,
      showConfirmDialog,
      pendingTransition,
      statusSteps,
      currentStepIndex,
      currentStatusLabel,
      availableTransitions,
      getStepColor,
      getStepIcon,
      getCurrentColor,
      confirmTransition,
      executeTransition,
      executeTransitionDirect,
      getStatusLabel,
    };
  },
});
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}

.gap-3 {
  gap: 12px;
}

:deep(.v-stepper-item) {
  flex-basis: auto !important;
}

:deep(.v-stepper-item__avatar) {
  margin: 0 auto 8px !important;
}

:deep(.v-stepper-item__content) {
  text-align: center;
}

:deep(.v-stepper-item--complete .v-stepper-item__avatar) {
  background-color: rgb(var(--v-theme-success)) !important;
}

:deep(.v-stepper-item--selected .v-stepper-item__avatar) {
  background-color: rgb(var(--v-theme-primary)) !important;
}

:deep(.v-stepper-item__avatar .v-icon) {
  color: white !important;
}
</style>
