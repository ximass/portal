export interface OrderForm {
  customer_id: string;
  delivery_type: string;
  markup: string;
  delivery_date: string;
  payment_obs: string;
}

export interface OrderSet {
  id?: number;
  name?: string;
  setParts: Part[];
  fileList: File[] | null;
}

export interface SetPartType {
  name: string;
  value: 'material' | 'sheet' | 'bar' | 'component' | 'process';
}

export interface Material {
  id: number;
  name: string;
  thickness: number;
  specific_weight: number;
  price_kg: number;
}

// Campos específicos de uma chapa
export interface Sheet {
  id: number;
  name: string;
  material_id: number;
  width: number;
  length: number;
}

// Campos específicos de uma barra
export interface Bar {
  id: number;
  name: string;
  length: number;
  weight: number;
  price_kg: number;
}

// Campos específicos de um componente
export interface Component {
  id: number;
  name: string;
  specification: string;
  unit_value: number;
  supplier: string;
}

export interface Part {
  id: number;
  set_id: number;
  type: SetPartType['value'];
  material_id: number | null;
  sheet_id: number | null;
  bar_id: number | null;
  component_id: number | null;
  title: string;
  content: string | null;
  quantity: number;
  unit_net_weight: number;
  unit_gross_weight: number;
  net_weight: number;
  gross_weight: number;
  unit_value: number;
  final_value: number;
  width: number;
  length: number;
  loss: number | null;
  markup: number | null;
  weights: number;
  obs?: string;
  processes?: any[];
}

export interface Set {
  id: number;
  name: string;
  parts: Part[];
}

export interface User {
  id: number;
  name: string;
  email: string;
  admin: boolean;
}

export interface Group {
  id: number;
  name: string;
  user_ids: number[];
}

export interface Process {
  id: number;
  title: string;
  content: string;
  value_per_minute: number;
}

export interface ProcessPivot {
  time: number;
  final_value: number;
}