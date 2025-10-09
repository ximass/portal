export type OrderType = 'pre_order' | 'order';

export interface OrderFilters {
  search: string;
  type: OrderType | null;
  dateFrom: string;
  dateTo: string;
}

export interface OrderForm {
  type: OrderType;
  customer_id: string;
  delivery_type: string;
  delivery_value: string;
  service_value: string;
  discount: string;
  markup: string;
  delivery_date: string;
  estimated_delivery_date: string;
  payment_obs: string;
  os_file?: File | null;
}

export interface Order {
  id: number;
  type: OrderType;
  customer_id: number | null;
  final_value: number | null;
  delivery_type: string | null;
  delivery_value: number | null;
  service_value: number | null;
  discount: number | null;
  markup: number | null;
  delivery_date: string | null;
  estimated_delivery_date: string | null;
  payment_obs: string | null;
  os_file?: string | null;
  customer?: Customer;
  sets?: Set[];
}

export interface OrderSet {
  id?: number;
  name?: string;
  quantity?: number | null;
  setParts: Part[];
  fileList: File[] | null;
}

export interface SetPartType {
  name: string;
  value: 'material' | 'sheet' | 'bar' | 'component' | 'process';
}

export interface Material {
  id: number | null;
  name: string;
  specific_weight: number;
  price_kg: number;
  ncm_id?: number | null;
  ncm?: MercosurCommonNomenclature;
}

// Campos específicos de uma chapa
export interface Sheet {
  id: number | null;
  name: string;
  material_id: number;
  thickness: number;
  width: number;
  length: number;
}

export interface SheetForm {
  id: number | null;
  name: string;
  material_id: number | null;
  thickness: number;
  width: number;
  length: number;
}

// Campos específicos de uma barra
export interface Bar {
  id: number | null;
  name: string;
  length: number;
  weight: number;
  price_kg: number;
  ncm_id?: number | null;
  ncm?: MercosurCommonNomenclature;
}

// Campos específicos de um componente
export interface Component {
  id: number | null;
  name: string;
  specification: string;
  unit_value: number;
  supplier: string;
  ncm_id?: number | null;
  ncm?: MercosurCommonNomenclature;
}

export interface Part {
  id: number;
  set_id: number;
  type: SetPartType['value'] | null;
  material_id: number | null;
  material?: Material;
  sheet_id: number | null;
  sheet?: Sheet;
  bar_id: number | null;
  bar?: Bar;
  component_id: number | null;
  component?: Component;
  ncm_id: number | null;
  ncm?: MercosurCommonNomenclature;
  set?: {
    id: number;
    order?: {
      id: number;
      customer?: {
        id: number;
        state?: State;
      };
    };
  };
  title: string;
  content: string | null;
  secondary_content?: string | null;
  quantity: number;
  unit: 'piece' | 'kg' | null;
  unit_net_weight: number;
  unit_gross_weight: number;
  net_weight: number;
  gross_weight: number;
  unit_value: number;
  final_value: number;
  unit_ipi_value: number;
  total_ipi_value: number;
  unit_icms_value: number;
  total_icms_value: number;
  thickness?: number;
  width: number;
  length: number;
  loss: number | null;
  markup: number | null;
  obs?: string;
  reference?: string;
  processes?: any[];
  locked_values?: string[];
}

export interface Set {
  id: number;
  name: string;
  content?: string | null;
  quantity?: number | null;
  unit?: 'piece' | 'kg' | null;
  ncm_id?: number | null;
  ncm?: MercosurCommonNomenclature;
  reference?: string | null;
  obs?: string | null;
  parts: Part[];
}

export interface SetForm {
  name: string;
  content?: string | null;
  quantity?: number | null;
  unit?: 'piece' | 'kg' | null;
  ncm_id?: number | null;
  reference?: string | null;
  obs?: string | null;
}

export interface User {
  id: number | null;
  name: string;
  email: string;
  admin: boolean;
  enabled: boolean;
}

export interface Permission {
  id: number | null;
  name: string;
  description: string | null;
}

export interface Group {
  id: number | null;
  name: string;
  user_ids: number[];
  users: User[];
  permission_ids?: number[];
  permissions?: Permission[];
}

export interface Process {
  id: number | null;
  title: string;
  content: string | null;
  value_per_minute: number;
}

export type ProcessForm = {
  title: string;
  content: string;
  value_per_minute: number;
};

export interface ProcessPivot {
  time: number;
  final_value: number;
}

export interface MercosurCommonNomenclature {
  id: number | null;
  code: string;
  ipi: number;
}

export type MercosurCommonNomenclatureForm = {
  code: string;
  ipi: number;
};

export interface State {
  id: number | null;
  name: string;
  abbreviation: string;
  icms: number;
}

export type StateForm = {
  name: string;
  abbreviation: string;
  icms: number;
};

export interface Customer {
  id: number | null;
  name: string;
  email: string | null;
  phone: string | null;
  cnpj: string | null;
  cpf: string | null;
  address: string | null;
  state_id: number | null;
  state?: State;
}

export type CustomerForm = {
  name: string;
  email: string | null;
  phone: string | null;
  cnpj: string | null;
  cpf: string | null;
  address: string | null;
  state_id: number | null;
};

export type ErrorLogLevel = 'error' | 'warning' | 'critical' | 'info';

export interface ErrorLog {
  id: number;
  level: ErrorLogLevel;
  message: string;
  exception: string | null;
  file: string | null;
  line: number | null;
  trace: string | null;
  url: string | null;
  method: string | null;
  request_data: any | null;
  ip: string | null;
  user_id: number | null;
  user?: User;
  created_at: string;
  updated_at: string;
}

export interface ErrorLogFilters {
  search: string;
  level: ErrorLogLevel | null;
  date_from: string;
  date_to: string;
  user_id: number | null;
}

export interface ErrorLogStatistics {
  total: number;
  by_level: {
    error?: number;
    warning?: number;
    critical?: number;
    info?: number;
  };
  last_24h: number;
  last_7days: number;
  last_30days: number;
}
