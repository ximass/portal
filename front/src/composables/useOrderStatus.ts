import type { AllOrderStatus, PreOrderStatus, OrderStatus } from '../types/types';

export function useOrderStatus() {
  const statusLabels: Record<AllOrderStatus, string> = {
    // Status de orçamento (pre_order)
    in_progress: 'Em andamento',
    waiting_response: 'Aguardando resposta',
    cancelled: 'Cancelado',
    approved: 'Aprovado',
    
    // Status de pedido (order)
    waiting_release: 'Aguardando liberação',
    released_for_production: 'Liberado para produção',
    finished: 'Finalizado',
  };

  const getStatusLabel = (status: AllOrderStatus): string => {
    return statusLabels[status] || status;
  };

  const getStatusColor = (status: AllOrderStatus): string => {
    const colorMap: Record<AllOrderStatus, string> = {
      in_progress: 'info',
      waiting_response: 'warning',
      cancelled: 'error',
      approved: 'success',
      waiting_release: 'info',
      released_for_production: 'primary',
      finished: 'success',
    };
    return colorMap[status] || 'grey';
  };

  const getPreOrderStatuses = (): Array<{ title: string; value: PreOrderStatus }> => {
    return [
      { title: 'Em andamento', value: 'in_progress' },
      { title: 'Aguardando resposta', value: 'waiting_response' },
      { title: 'Cancelado', value: 'cancelled' },
      { title: 'Aprovado', value: 'approved' },
    ];
  };

  const getOrderStatuses = (): Array<{ title: string; value: OrderStatus }> => {
    return [
      { title: 'Aguardando liberação', value: 'waiting_release' },
      { title: 'Liberado para produção', value: 'released_for_production' },
      { title: 'Finalizado', value: 'finished' },
    ];
  };

  const getAllStatuses = (): Array<{ title: string; value: AllOrderStatus }> => {
    return [
      ...getPreOrderStatuses(),
      ...getOrderStatuses(),
    ];
  };

  return {
    statusLabels,
    getStatusLabel,
    getStatusColor,
    getPreOrderStatuses,
    getOrderStatuses,
    getAllStatuses,
  };
}
