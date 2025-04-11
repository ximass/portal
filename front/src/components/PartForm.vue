<template>
  <v-dialog :model-value="show" @update:model-value="(value) => { if (!value) closeDialog(); }" width="80vw" height="90vh">
    <v-card>
      <v-card-title>
        <v-text-field variant="underlined" v-model="localPart.title" required />
      </v-card-title>
      <v-card-text>
        <v-row>
          <!-- Left: Image panel -->
          <v-col cols="6">
            <v-responsive max-height="60vh" max-width="40vw" class="overflow-auto">
              <v-img v-if="part?.content" :src="getPartImageUrl(part.content)" contain max-width="100%" />
              <div v-else>Sem imagem para exibir</div>
            </v-responsive>
          </v-col>
          <!-- Right: Form panel -->
          <v-col cols="6" class="dense-form">
            <!-- Card 0: Seletor do tipo de part -->
            <v-row dense>
              <v-col cols="12">
                <v-select
                  label="Tipo da peça"
                  :items="setPartTypes"
                  item-title="name"
                  item-value="value"
                  v-model="localPart.type"
                  required density="compact"
                  @update:modelValue="onTypeChange"
                />
              </v-col>
            </v-row>

            <!-- Para 'material', 'sheet' e 'bar', exibe seletor de Material -->
            <v-row dense v-if="localPart.type === 'material' || localPart.type === 'sheet' || localPart.type === 'bar'">
              <v-col cols="12">
                <v-select
                  label="Material"
                  :items="materials"
                  item-title="name"
                  item-value="id"
                  v-model="selectedMaterial"
                  required density="compact"
                  @update:modelValue="fillMaterialDetails(selectedMaterial)"
                />
              </v-col>
            </v-row>

            <!-- Para 'sheet' exibe seletor de Chapa -->
            <v-row dense v-if="localPart.type === 'sheet'">
              <v-col cols="12">
                <v-select
                  label="Chapa"
                  :items="sheets"
                  item-title="name"
                  item-value="id"
                  v-model="selectedSheet"
                  required density="compact"
                  @update:modelValue="fillSheetDetails(selectedSheet)"
                />
              </v-col>
            </v-row>

            <!-- Para 'bar' exibe seletor de Barra -->
            <v-row dense v-if="localPart.type === 'bar'">
              <v-col cols="12">
                <v-select
                  label="Barra"
                  :items="bars"
                  item-title="name"
                  item-value="id"
                  v-model="selectedBar"
                  required density="compact"
                  @update:modelValue="fillBarDetails(selectedBar)"
                />
              </v-col>
            </v-row>

            <!-- Para 'component' exibe seletor de Componente -->
            <v-row dense v-if="localPart.type === 'component'">
              <v-col cols="12">
                <v-select
                  label="Componente"
                  :items="components"
                  item-title="name"
                  item-value="id"
                  v-model="selectedComponent"
                  required density="compact"
                  @update:modelValue="fillComponentDetails(selectedComponent)"
                />
              </v-col>
            </v-row>

            <!-- Campos Complementares para material, sheet e bar -->
            <v-card v-if="localPart.type === 'material' || localPart.type === 'sheet' || localPart.type === 'bar'" class="pa-4">
              <v-row dense>
                <template v-if="localPart.type === 'material' || localPart.type === 'sheet'">
                  <v-col cols="12" md="4" small="6">
                    <v-text-field label="Largura" v-model="localPart.width" type="number" required density="compact"
                      @blur="localPart.width = roundValue(localPart.width, 2)" suffix="mm"/>
                  </v-col>
                </template>
                <template v-if="localPart.type === 'bar'">
                  <v-col cols="12" md="4" small="6">
                    <v-text-field label="Diâmetro" v-model="localPart.diameter" type="number" required density="compact"
                      @blur="localPart.diameter = roundValue(localPart.diameter, 2)" suffix="mm"/>
                  </v-col>
                </template>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Comprimento" v-model="localPart.length" type="number" required density="compact"
                    @blur="localPart.length = roundValue(localPart.length, 2)" suffix="mm"/>
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Perda" v-model="localPart.loss" type="number" required density="compact"
                    @blur="localPart.loss = roundValue(localPart.loss, 2)" suffix="%"/>
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso líquido unitário" v-model="localPart.unit_net_weight" type="number" required density="compact"
                    @blur="localPart.unit_net_weight = roundValue(localPart.unit_net_weight, 2)" suffix="KG"/>
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso bruto unitário" v-model="localPart.unit_gross_weight" type="number" required density="compact"
                    @blur="localPart.unit_gross_weight = roundValue(localPart.unit_gross_weight, 2)" suffix="KG"/>
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso líquido" v-model="localPart.net_weight" type="number" required density="compact"
                    @blur="localPart.net_weight = roundValue(localPart.net_weight, 2)" suffix="KG"/>
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso bruto" v-model="localPart.gross_weight" type="number" required density="compact"
                    @blur="localPart.gross_weight = roundValue(localPart.gross_weight, 2)" suffix="KG"/>
                </v-col>
              </v-row>
            </v-card>

            <!-- Campo Complementar para component -->
            <v-card v-if="localPart.type === 'component'" class="pa-4">
              <v-row dense>
                <v-col cols="12">
                  <v-text-field label="Markup" v-model="localPart.markup" type="number" required density="compact"
                    @blur="localPart.markup = roundValue(localPart.markup, 3)" suffix="%"/>
                </v-col>
              </v-row>
            </v-card>

            <!-- Campos que sempre estarão presentes -->
            <v-card class="pa-4">
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field
                    label="Quantidade"
                    v-model="localPart.quantity"
                    type="number"
                    required
                    density="compact"
                    @blur="localPart.quantity = roundValue(localPart.quantity, 0)"
                  />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Valor unitário" v-model="localPart.unit_value" type="number" required density="compact"
                    @blur="localPart.unit_value = roundValue(localPart.unit_value, 2)" prefix="R$"/>
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Valor final" v-model="localPart.final_value" type="number" required density="compact"
                    @blur="localPart.final_value = roundValue(localPart.final_value, 2)" prefix="R$"/>
                </v-col>
              </v-row>
            </v-card>

            <ProcessMultiField v-model="localPart.processes" />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn color="white" variant="flat" @click="closeDialog">Fechar</v-btn>
        <v-btn color="primary" variant="flat" @click="savePart">Salvar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, PropType, ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { Part, Material, Sheet, Bar, Component } from '@/types/types';
import ProcessMultiField from '@/components/ProcessMultiField.vue';

export default defineComponent({
  name: 'PartForm',
  components: { ProcessMultiField },
  props: {
    show: { type: Boolean, required: true },
    part: { type: Object as PropType<Part>, default: null },
    getPartImageUrl: { type: Function as PropType<(part: any) => string>, required: true }
  },
  emits: ['part-saved', 'close'],
  setup(props, { emit }) {
    const { showToast } = useToast();

    const localPart = ref<Part>(props.part ? { ...props.part } : {
      id: '',
      set_id: '',
      type: '',
      material_id: null,
      sheet_id: null,
      bar_id: null,
      component_id: null,
      title: '',
      content: '',
      quantity: 0,
      unit_net_weight: 0,
      unit_gross_weight: 0,
      net_weight: 0,
      gross_weight: 0,
      unit_value: 0,
      final_value: 0,
      width: 0,
      length: 0,
      diameter: 0,
      loss: 0,
      markup: 0,
      processes: []
    });

    // Flag to indicate if the component has mounted
    const isMounted = ref(false);

    const setPartTypes = [
      { name: 'Material', value: 'material' },
      { name: 'Chapa', value: 'sheet' },
      { name: 'Barra', value: 'bar' },
      { name: 'Componente', value: 'component' }
    ];

    const materials = ref<Material[]>([]);
    const sheets = ref<Sheet[]>([]);
    const bars = ref<Bar[]>([]);
    const components = ref<Component[]>([]);

    const selectedMaterial = ref<number | null>(null);
    const selectedSheet = ref<number | null>(null);
    const selectedBar = ref<number | null>(null);
    const selectedComponent = ref<number | null>(null);

    const roundValue = (value: number, decimals: number): number =>
      isNaN(value) ? 0 : Number(parseFloat(value.toString()).toFixed(decimals));

    const fetchMaterials = async () => {
      try {
        const { data } = await axios.get('/api/materials');
        materials.value = data;
      } catch (error) {
        showToast('Erro ao buscar materiais', 'error');
      }
    };

    const fetchSheets = async () => {
      try {
        const { data } = await axios.get('/api/sheets');
        sheets.value = data;
      } catch (error) {
        showToast('Erro ao buscar chapas', 'error');
      }
    };

    const fetchBars = async () => {
      try {
        const { data } = await axios.get('/api/bars');
        bars.value = data;
      } catch (error) {
        showToast('Erro ao buscar barras', 'error');
      }
    };

    const fetchComponents = async () => {
      try {
        const { data } = await axios.get('/api/components');
        components.value = data;
      } catch (error) {
        showToast('Erro ao buscar componentes', 'error');
      }
    };

    const fillMaterialDetails = async (materialId: number | null) => {
      if (!materialId) return;
      try {
        const { data } = await axios.get(`/api/materials/${materialId}`);
        localPart.value.material_id = data.id;
      } catch (error) {
        showToast('Erro ao buscar material', 'error');
      }
    };

    const fillSheetDetails = async (sheetId: number | null) => {
      if (!sheetId) return;
      try {
        const { data } = await axios.get(`/api/sheets/${sheetId}`);
        localPart.value.sheet_id = data.id;
        localPart.value.width = data.width;
        localPart.value.length = data.length;
      } catch (error) {
        showToast('Erro ao buscar chapa', 'error');
      }
    };

    const fillBarDetails = async (barId: number | null) => {
      if (!barId) return;
      try {
        const { data } = await axios.get(`/api/bars/${barId}`);
        localPart.value.bar_id = data.id;
        localPart.value.width = data.width;
        localPart.value.length = data.length;
      } catch (error) {
        showToast('Erro ao buscar barra', 'error');
      }
    };

    const fillComponentDetails = async (componentId: number | null) => {
      if (!componentId) return;
      try {
        const { data } = await axios.get(`/api/components/${componentId}`);
        localPart.value.component_id = data.id;
        localPart.value.markup = data.unit_value;
      } catch (error) {
        showToast('Erro ao buscar componente', 'error');
      }
    };

    const onTypeChange = (newType: string) => {
      selectedMaterial.value = null;
      selectedSheet.value = null;
      selectedBar.value = null;
      selectedComponent.value = null;

      // Carregar os dados conforme o novo tipo
      if (newType === 'material' || newType === 'sheet' || newType === 'bar') {
        fetchMaterials();
      }
      if (newType === 'sheet') fetchSheets();
      if (newType === 'bar') fetchBars();
      if (newType === 'component') fetchComponents();
    };

    const calculateProperties = async () => {
      try {
        const { data } = await axios.post('/api/set-parts/calculateProperties', {
          part: localPart.value
        });
        localPart.value.unit_net_weight = data.unit_net_weight;
        localPart.value.net_weight = data.net_weight;
        localPart.value.unit_gross_weight = data.unit_gross_weight;
        localPart.value.gross_weight = data.gross_weight;
        localPart.value.unit_value = data.unit_value;
        localPart.value.final_value = data.final_value;
      } catch (error) {
        showToast('Erro ao calcular propriedades', 'error');
      }
    };

    const savePart = async () => {
      if (!localPart.value.id || !localPart.value.set_id) return;
      try {
        const payload = { 
          ...localPart.value,
          material_id: localPart.value.type === 'component' ? null : selectedMaterial.value,
          sheet_id: localPart.value.type === 'sheet' ? selectedSheet.value : null,
          bar_id: localPart.value.type === 'bar' ? selectedBar.value : null,
          component_id: localPart.value.type === 'component' ? selectedComponent.value : null
        };
        await axios.put(`/api/sets/${localPart.value.set_id}/parts/${localPart.value.id}`, payload);
        emit('part-saved', localPart.value);
        emit('close');
      } catch (error) {
        showToast('Erro ao salvar a peça: ' + error, 'error');
      }
    };

    const closeDialog = () => {
      emit('close');
    };

    // Atualiza o formulário quando a prop "part" é modificada
    watch(() => props.part, (newVal) => {
      if (newVal) {
        localPart.value = { ...newVal };
        // Chamar onTypeChange apenas se o componente já foi montado
        if (isMounted.value && localPart.value.type) {
          onTypeChange(localPart.value.type);
        }
      }
    }, { deep: true });

    watch(
      () => [
        localPart.value.width,
        localPart.value.length,
        localPart.value.diameter,
        localPart.value.quantity,
        localPart.value.unit_net_weight,
        localPart.value.unit_gross_weight,
        localPart.value.loss
      ],
      () => {
        calculateProperties();
      }
    );

    onMounted(() => {
      isMounted.value = true;
      
      if (localPart.value.type) {
          (async () => {
              if (localPart.value.type === 'material' || localPart.value.type === 'sheet' || localPart.value.type === 'bar') {
                  await fetchMaterials();
              }
              if (localPart.value.type === 'sheet') {
                  await fetchSheets();
              }
              if (localPart.value.type === 'bar') {
                  await fetchBars();
              }
              if (localPart.value.type === 'component') {
                  await fetchComponents();
              }
              
              selectedMaterial.value = localPart.value.material_id;
              selectedSheet.value = localPart.value.sheet_id;
              selectedBar.value = localPart.value.bar_id;
              selectedComponent.value = localPart.value.component_id;
          })();
      }
});

    return {
      localPart,
      setPartTypes,
      materials,
      sheets,
      bars,
      components,
      selectedMaterial,
      selectedSheet,
      selectedBar,
      selectedComponent,
      roundValue,
      onTypeChange,
      fillMaterialDetails,
      fillSheetDetails,
      fillBarDetails,
      fillComponentDetails,
      savePart,
      closeDialog,
      show: props.show,
      getPartImageUrl: props.getPartImageUrl
    };
  }
});
</script>

<style>
.dense-form .v-row {
  margin-bottom: -20px;
}
</style>