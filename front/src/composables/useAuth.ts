import { ref, computed } from 'vue';
import axios from 'axios';
import type { User, Permission } from '../types/types';

const currentUser = ref<User | null>(null);
const userPermissions = ref<Permission[]>([]);

export function useAuth() {
  /**
   * Carrega o usuário atual e suas permissões
   */
  const loadCurrentUser = async () => {
    try {
      const response = await axios.get('/api/user');
      currentUser.value = response.data;
      
      // Se o usuário tem grupos, buscar suas permissões
      if (currentUser.value && !currentUser.value.admin) {
        const permissionsResponse = await axios.get('/api/user/permissions');
        userPermissions.value = permissionsResponse.data;
      }
    } catch (error) {
      console.error('Erro ao carregar usuário:', error);
      currentUser.value = null;
      userPermissions.value = [];
    }
  };

  /**
   * Verifica se o usuário tem uma permissão específica
   */
  const hasPermission = (permissionName: string): boolean => {
    if (!currentUser.value) return false;
    
    // Admin tem todas as permissões
    if (currentUser.value.admin) return true;
    
    // Verifica se o usuário tem a permissão
    return userPermissions.value.some(p => p.name === permissionName);
  };

  /**
   * Verifica se o usuário tem alguma das permissões listadas
   */
  const hasAnyPermission = (permissions: string[]): boolean => {
    if (!currentUser.value) return false;
    
    // Admin tem todas as permissões
    if (currentUser.value.admin) return true;
    
    // Verifica se o usuário tem alguma das permissões
    return permissions.some(permission => hasPermission(permission));
  };

  /**
   * Verifica se o usuário pode editar um pedido baseado no tipo
   */
  const canEditOrder = (orderType: 'order' | 'pre_order'): boolean => {
    if (!currentUser.value) return false;
    
    if (currentUser.value.admin) return true;
    
    const permission = orderType === 'order' ? 'edit_orders' : 'edit_pre_orders';
    return hasPermission(permission);
  };

  /**
   * Verifica se o usuário pode deletar pedidos
   */
  const canDeleteOrder = (): boolean => {
    if (!currentUser.value) return false;
    
    if (currentUser.value.admin) return true;
    
    return hasPermission('delete_orders');
  };

  /**
   * Verifica se o usuário pode visualizar todos os pedidos
   */
  const canViewAllOrders = (): boolean => {
    if (!currentUser.value) return false;
    
    if (currentUser.value.admin) return true;
    
    return hasPermission('view_all_orders');
  };

  /**
   * Verifica se o usuário é admin
   */
  const isAdmin = computed(() => {
    return currentUser.value?.admin ?? false;
  });

  return {
    currentUser,
    userPermissions,
    loadCurrentUser,
    hasPermission,
    hasAnyPermission,
    canEditOrder,
    canDeleteOrder,
    canViewAllOrders,
    isAdmin
  };
}
