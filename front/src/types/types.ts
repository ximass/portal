export interface MaterialType {
  name: string;
  value: 'sheet' | 'bar' | 'component';
}

// Define a interface base, compartilhada pelas variações
export interface BaseMaterial {
  id: number;
  name: string;
  type: 'sheet' | 'bar' | 'component';
}

// Campos específicos de uma chapa
export interface Sheet extends BaseMaterial {
  type: 'sheet';
  thickness: number;
  width: number;
  length: number;
  specific_weight: number;
  price_per_gram: number;
}

// Campos específicos de uma barra
export interface Bar extends BaseMaterial {
  type: 'bar';
  diameter: number;
  length: number;
  specific_weight: number;
  price_per_gram: number;
}

// Campos específicos de um componente
export interface Component extends BaseMaterial {
  type: 'component';
  specification: string;
  unit_value: number;
}

// Material é uma união das interfaces específicas
export type Material = Sheet | Bar | Component;

export interface Part {
  id: string;
  set_id: string;
  title: string;
  content: string;
  material_id: string;
  quantity: number;
  unit_net_weight: number;
  unit_gross_weight: number;
  net_weight: number;
  net_gross_weight: number;
  unit_value: number;
  final_value: number;
  width: number;
  length: number;
  loss: number;
  markup: number;
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
  fixed_value: number;
}

export interface ProcessSelection {
  id: number | null;
  time: number;
  quantity: number;
}