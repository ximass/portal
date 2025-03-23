export interface MaterialType {
    name: string;
    value: 'sheet' | 'bar' | 'component';
  }
  
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