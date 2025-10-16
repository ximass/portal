/**
 * Composable para gerenciar permissões e suas traduções
 */

export interface PermissionTranslation {
  name: string;
  title: string;
  description: string;
}

// Mapeamento de traduções das permissões
const permissionTranslations: Record<string, PermissionTranslation> = {
  edit_orders: {
    name: 'edit_orders',
    title: 'Editar pedidos',
    description: 'Permite editar pedidos'
  },
  edit_pre_orders: {
    name: 'edit_pre_orders',
    title: 'Editar orçamentos',
    description: 'Permite editar orçamentos'
  },
  delete_orders: {
    name: 'delete_orders',
    title: 'Excluir pedidos',
    description: 'Permite excluir pedidos e orçamentos'
  },
  delete_pre_orders: {
    name: 'delete_pre_orders',
    title: 'Excluir orçamentos',
    description: 'Permite excluir orçamentos'
  },
  approve_pre_orders: {
    name: 'approve_pre_orders',
    title: 'Aprovar orçamentos',
    description: 'Permite aprovar orçamentos'
  }
};

export function usePermissions() {
  /**
   * Traduz o nome de uma permissão para português
   */
  const translatePermission = (permissionName: string): string => {
    return permissionTranslations[permissionName]?.title || permissionName;
  };

  /**
   * Obtém a descrição traduzida de uma permissão
   */
  const getPermissionDescription = (permissionName: string): string => {
    return permissionTranslations[permissionName]?.description || '';
  };

  /**
   * Obtém a tradução completa de uma permissão
   */
  const getPermissionTranslation = (permissionName: string): PermissionTranslation => {
    return permissionTranslations[permissionName] || {
      name: permissionName,
      title: permissionName,
      description: ''
    };
  };

  /**
   * Verifica se uma permissão tem tradução
   */
  const hasTranslation = (permissionName: string): boolean => {
    return permissionName in permissionTranslations;
  };

  return {
    translatePermission,
    getPermissionDescription,
    getPermissionTranslation,
    hasTranslation
  };
}
