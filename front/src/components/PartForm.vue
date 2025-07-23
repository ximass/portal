<template>
  <div class="part-form-container">
    <v-btn
      v-if="showNavigation && !isFirstPart"
      class="navigation-arrow navigation-arrow-left"
      icon
      size="large"
      color="primary"
      @click="navigateToPrevious"
    >
      <v-icon>mdi-chevron-left</v-icon>
    </v-btn>

    <v-dialog
      :model-value="show"
      @update:model-value="
        value => {
          if (!value) closeDialog();
        }
      "
      width="80vw"
      height="90vh"
    >
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <v-text-field
            variant="underlined"
            v-model="localPart.title"
            required
          />
          <button
            class="close-btn"
            @click="closeDialog"
            aria-label="Fechar"
            title="Fechar"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              height="24"
              width="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </v-card-title>
        <v-card-text>
          <v-row>
            <!-- Left: Image panel -->
            <v-col cols="6">
              <v-responsive
                max-height="60vh"
                max-width="40vw"
                min-height="50vh"
              >
                <template v-if="localPart.content">
                  <template v-if="isPdf(localPart.content)">
                    <iframe
                      :src="getPartImageUrl(localPart.content)"
                      width="100%"
                      height="100%"
                    />
                  </template>
                  <template v-else>
                    <v-img
                      :src="getPartImageUrl(localPart.content)"
                      contain
                      max-width="100%"
                    />
                  </template>
                </template>
                <div v-else>Sem imagem para exibir</div>
              </v-responsive>
              <!-- Upload secundário-->
              <div class="mt-4">
                <v-file-input
                  v-model="secondaryFile"
                  accept="image/*,.pdf"
                  density="compact"
                  show-size
                  prepend-icon="mdi-upload"
                  label="Imagem secundária"
                  @change="onSecondaryFileChange"
                  clearable
                  @click:clear="onSecondaryFileDelete"
                >
                  <template #selection>
                    <span v-if="localPart.secondary_content">
                      Imagem carregada.
                    </span>
                  </template>
                </v-file-input>
              </div>
            </v-col>
            <!-- Right: Form panel -->
            <v-col cols="6" class="dense-form">
              <v-row dense style="margin-top: -40px">
                <v-col cols="12">
                  <v-text-field
                    label="Referência"
                    v-model="localPart.obs"
                    variant="underlined"
                    clearable
                  />
                </v-col>
              </v-row>

              <!-- Card 0: Seletor do tipo de part -->
              <v-row dense>
                <v-col cols="12">
                  <v-select
                    label="Tipo da peça"
                    :items="setPartTypes"
                    item-title="name"
                    item-value="value"
                    v-model="localPart.type"
                    required
                    density="compact"
                    @update:modelValue="onTypeChange"
                  />
                </v-col>
              </v-row>

              <!-- Para 'material', 'sheet' e 'bar', exibe seletor de Material -->
              <v-row
                dense
                v-if="
                  localPart.type === 'material' || localPart.type === 'sheet'
                "
              >
                <v-col cols="12">
                  <v-select
                    label="Material"
                    :items="materials"
                    item-title="name"
                    item-value="id"
                    v-model="selectedMaterial"
                    required
                    density="compact"
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
                    required
                    density="compact"
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
                    required
                    density="compact"
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
                    required
                    density="compact"
                    @update:modelValue="fillComponentDetails(selectedComponent)"
                  />
                </v-col>
              </v-row>

              <!-- Campos Complementares para material, sheet e bar -->
              <template
                v-if="
                  localPart.type === 'material' ||
                  localPart.type === 'sheet' ||
                  localPart.type === 'bar'
                "
                class="pa-4"
              >
                <v-row dense>
                  <template
                    v-if="
                      localPart.type === 'material' ||
                      localPart.type === 'sheet'
                    "
                  >
                    <v-col cols="12" md="4" small="6" v-if="localPart.type === 'material'">
                      <v-text-field
                        label="Espessura"
                        v-model="localPart.thickness"
                        type="number"
                        required
                        density="compact"
                        @change="calculateProperties"
                        @blur="localPart.thickness = roundValue(localPart.thickness ?? 0, 2)"
                        suffix="mm"
                      />
                    </v-col>
                    <v-col cols="12" md="4" small="6">
                      <v-text-field
                        label="Largura"
                        v-model="localPart.width"
                        type="number"
                        required
                        density="compact"
                        @change="calculateProperties"
                        @blur="localPart.width = roundValue(localPart.width, 2)"
                        suffix="mm"
                      />
                    </v-col>
                  </template>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Comprimento"
                      v-model="localPart.length"
                      type="number"
                      required
                      density="compact"
                      @change="calculateProperties"
                      @blur="localPart.length = roundValue(localPart.length, 2)"
                      suffix="mm"
                    />
                  </v-col>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Perda"
                      v-model="localPart.loss"
                      type="number"
                      required
                      density="compact"
                      @change="calculateProperties"
                      @blur="
                        localPart.loss = roundValue(localPart.loss ?? 0, 2)
                      "
                      suffix="%"
                    />
                  </v-col>
                </v-row>
                <v-row dense>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Peso líquido unitário"
                      v-model="localPart.unit_net_weight"
                      type="number"
                      required
                      density="compact"
                      @change="onUnitNetWeightChange"
                      @blur="
                        localPart.unit_net_weight = roundValue(
                          localPart.unit_net_weight,
                          2
                        )
                      "
                      suffix="KG"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('unit_net_weight')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Peso bruto unitário"
                      v-model="localPart.unit_gross_weight"
                      type="number"
                      required
                      density="compact"
                      @change="onUnitGrossWeightChange"
                      @blur="
                        localPart.unit_gross_weight = roundValue(
                          localPart.unit_gross_weight,
                          2
                        )
                      "
                      suffix="KG"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('unit_gross_weight')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Peso líquido"
                      v-model="localPart.net_weight"
                      type="number"
                      required
                      density="compact"
                      @change="onNetWeightChange"
                      @blur="
                        localPart.net_weight = roundValue(
                          localPart.net_weight,
                          2
                        )
                      "
                      suffix="KG"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('net_weight')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>
                <v-row dense>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Peso bruto"
                      v-model="localPart.gross_weight"
                      type="number"
                      required
                      density="compact"
                      @change="onGrossWeightChange"
                      @blur="
                        localPart.gross_weight = roundValue(
                          localPart.gross_weight,
                          2
                        )
                      "
                      suffix="KG"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('gross_weight')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>
              </template>

              <!-- Campo Complementar para component -->
              <template v-if="localPart.type === 'component'" class="pa-4">
                <v-row dense>
                  <v-col cols="12">
                    <v-text-field
                      label="Markup"
                      v-model="localPart.markup"
                      type="number"
                      required
                      density="compact"
                      @change="calculateProperties"
                      @blur="
                        localPart.markup = roundValue(localPart.markup ?? 0, 3);
                        calculateProperties();
                      "
                      suffix="%"
                    />
                  </v-col>
                </v-row>
              </template>

              <!-- Campos que sempre estarão presentes -->
              <v-sheet>
                <v-row dense>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Quantidade"
                      v-model="localPart.quantity"
                      type="number"
                      required
                      density="compact"
                      @change="calculateProperties"
                      @blur="
                        localPart.quantity = roundValue(localPart.quantity, 0)
                      "
                    />
                  </v-col>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Valor unitário"
                      v-model="localPart.unit_value"
                      type="number"
                      required
                      density="compact"
                      @change="onUnitValueChange"
                      prefix="R$"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('unit_value')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                  <v-col cols="12" md="4" small="6">
                    <v-text-field
                      label="Valor final"
                      v-model="localPart.final_value"
                      type="number"
                      required
                      density="compact"
                      @change="onFinalValueChange"
                      @blur="
                        localPart.final_value = roundValue(
                          localPart.final_value,
                          2
                        )
                      "
                      prefix="R$"
                    >
                      <template
                        #append-inner
                        v-if="lockedValues.includes('final_value')"
                      >
                        <v-icon title="Valor travado devido à edição manual"
                          >mdi-lock</v-icon
                        >
                      </template>
                    </v-text-field>
                  </v-col>
                </v-row>
              </v-sheet>

              <ProcessMultiField
                v-model="localPart.processes"
                @process-updated="calculateProperties"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="white" variant="flat" @click="recalculatePart"
            >Recalcular</v-btn
          >
          <v-btn color="primary" variant="flat" @click="savePart">Salvar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-btn
      v-if="showNavigation && !isLastPart"
      class="navigation-arrow navigation-arrow-right"
      icon
      size="large"
      color="primary"
      @click="navigateToNext"
    >
      <v-icon>mdi-chevron-right</v-icon>
    </v-btn>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  type PropType,
  ref,
  watch,
  onMounted,
  nextTick,
  computed,
} from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { Part, Material, Sheet, Bar, Component } from '../types/types';
import ProcessMultiField from '../components/ProcessMultiField.vue';

export default defineComponent({
  name: 'PartForm',
  components: { ProcessMultiField },
  props: {
    show: { type: Boolean, required: true },
    part: { type: Object as PropType<Part>, default: null },
    allParts: { type: Array as PropType<Part[]>, default: () => [] },
    getPartImageUrl: {
      type: Function as PropType<(part: any) => string>,
      required: true,
    },
    currentPartIndex: { type: Number, default: -1 },
    showNavigation: { type: Boolean, default: false },
  },
  emits: ['part-saved', 'close', 'navigate-to-part'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    const setPartTypes = [
      { name: 'Material', value: 'material' },
      { name: 'Chapa', value: 'sheet' },
      { name: 'Barra', value: 'bar' },
      { name: 'Componente', value: 'component' },
      { name: 'Processos', value: 'process' },
    ];

    const localPart = ref<Part>(props.part ? { ...props.part } : ({} as Part));

    const lockedValues = ref<string[]>(props.part?.locked_values ?? []);
    const isMounted = ref(false);

    const materials = ref<Material[]>([]);
    const sheets = ref<Sheet[]>([]);
    const bars = ref<Bar[]>([]);
    const components = ref<Component[]>([]);

    const selectedMaterial = ref<number | null>(null);
    const selectedSheet = ref<number | null>(null);
    const selectedBar = ref<number | null>(null);
    const selectedComponent = ref<number | null>(null);

    function roundPartValues(part: Part): Part {
      return {
        ...part,
        unit_net_weight: roundValue(part.unit_net_weight, 2),
        net_weight: roundValue(part.net_weight, 2),
        unit_gross_weight: roundValue(part.unit_gross_weight, 2),
        gross_weight: roundValue(part.gross_weight, 2),
        unit_value: roundValue(part.unit_value, 2),
        final_value: roundValue(part.final_value, 2),
        width: roundValue(part.width, 2),
        length: roundValue(part.length, 2),
        loss:
          part.loss !== null && part.loss !== undefined
            ? roundValue(part.loss, 2)
            : null,
        markup:
          part.markup !== null && part.markup !== undefined
            ? roundValue(part.markup, 3)
            : null,
        quantity: Math.round(part.quantity),
      };
    }

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
        localPart.value.thickness = data.thickness;
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
        localPart.value.unit_value = data.unit_value;
      } catch (error) {
        showToast('Erro ao buscar componente', 'error');
      }
    };

    const onTypeChange = (newType: string) => {
      selectedMaterial.value = null;
      selectedSheet.value = null;
      selectedBar.value = null;
      selectedComponent.value = null;

      localPart.value.material_id = null;
      localPart.value.sheet_id = null;
      localPart.value.bar_id = null;
      localPart.value.component_id = null;
      localPart.value.thickness = undefined;

      if (newType === 'material' || newType === 'sheet') {
        fetchMaterials();
      }
      if (newType === 'sheet') fetchSheets();
      if (newType === 'bar') fetchBars();
      if (newType === 'component') fetchComponents();
    };

    const onUnitValueChange = () => {
      if (!lockedValues.value.includes('unit_value')) {
        lockedValues.value.push('unit_value');
      }

      calculateProperties();
    };

    const onFinalValueChange = () => {
      if (!lockedValues.value.includes('final_value')) {
        lockedValues.value.push('final_value');
      }

      calculateProperties();
    };

    const onUnitNetWeightChange = () => {
      if (!lockedValues.value.includes('unit_net_weight')) {
        lockedValues.value.push('unit_net_weight');
      }
      calculateProperties();
    };

    const onUnitGrossWeightChange = () => {
      if (!lockedValues.value.includes('unit_gross_weight')) {
        lockedValues.value.push('unit_gross_weight');
      }
      calculateProperties();
    };

    const onNetWeightChange = () => {
      if (!lockedValues.value.includes('net_weight')) {
        lockedValues.value.push('net_weight');
      }
      calculateProperties();
    };

    const onGrossWeightChange = () => {
      if (!lockedValues.value.includes('gross_weight')) {
        lockedValues.value.push('gross_weight');
      }
      calculateProperties();
    };

    const recalculatePart = () => {
      lockedValues.value = [];
      calculateProperties();
    };

    const calculateProperties = async () => {
      try {
        const { data } = await axios.post(
          '/api/set-parts/calculateProperties',
          {
            part: {
              ...localPart.value,
              locked_values: lockedValues.value,
            },
          }
        );

        const rounded = roundPartValues(data);

        localPart.value.unit_net_weight = rounded.unit_net_weight;
        localPart.value.net_weight = rounded.net_weight;
        localPart.value.unit_gross_weight = rounded.unit_gross_weight;
        localPart.value.gross_weight = rounded.gross_weight;
        localPart.value.unit_value = rounded.unit_value;
        localPart.value.final_value = rounded.final_value;
        localPart.value.width = rounded.width;
        localPart.value.length = rounded.length;
        localPart.value.loss = rounded.loss;
        localPart.value.markup = rounded.markup;
        localPart.value.quantity = rounded.quantity;
      } catch (error) {
        showToast('Erro ao calcular propriedades', 'error');
      }
    };

    const savePart = async () => {
      if (
        !localPart.value.id ||
        !localPart.value.set_id ||
        !localPart.value.type
      ) {
        showToast('Erro: A peça não possui tipo definido', 'error');
        return;
      }

      try {
        const payload = {
          ...localPart.value,
          material_id:
            localPart.value.type === 'material' ||
            localPart.value.type === 'sheet'
              ? selectedMaterial.value
              : null,
          sheet_id:
            localPart.value.type === 'sheet' ? selectedSheet.value : null,
          bar_id: localPart.value.type === 'bar' ? selectedBar.value : null,
          component_id:
            localPart.value.type === 'component'
              ? selectedComponent.value
              : null,
          locked_values: lockedValues.value,
        };

        await axios.put(
          `/api/sets/${localPart.value.set_id}/parts/${localPart.value.id}`,
          payload
        );

        emit('part-saved', {
          ...localPart.value,
          locked_values: lockedValues.value,
        });
      } catch (error) {
        showToast('Erro ao salvar a peça: ' + error, 'error');
      }
    };

    const closeDialog = () => {
      emit('close');
    };

    const loadDataBasedOnPartType = async (part: Part) => {
      if (!part.type) return;

      selectedMaterial.value = null;
      selectedSheet.value = null;
      selectedBar.value = null;
      selectedComponent.value = null;

      if (part.type === 'material' || part.type === 'sheet') {
        await fetchMaterials();
      }
      if (part.type === 'sheet') {
        await fetchSheets();
      }
      if (part.type === 'bar') {
        await fetchBars();
      }
      if (part.type === 'component') {
        await fetchComponents();
      }

      selectedMaterial.value = part.material_id;
      selectedSheet.value = part.sheet_id;
      selectedBar.value = part.bar_id;
      selectedComponent.value = part.component_id;
    };

    // Atualiza o formulário quando a prop "part" é modificada
    watch(
      () => props.part,
      async newVal => {
        if (newVal) {
          localPart.value = { ...newVal };
          lockedValues.value = Array.isArray(newVal.locked_values)
            ? [...newVal.locked_values]
            : [];
          secondaryFile.value = newVal.secondary_content
            ? new File([], newVal.secondary_content)
            : null;

          await loadDataBasedOnPartType(newVal);
        }
      }
    );

    onMounted(async () => {
      isMounted.value = true;

      if (localPart.value.type) {
        await loadDataBasedOnPartType(localPart.value);
      }

      if (localPart.value.id) {
        secondaryFile.value = localPart.value.secondary_content
          ? new File([], localPart.value.secondary_content)
          : null;
      }
    });

    const secondaryFile = ref<File | null>(null);

    const onSecondaryFileChange = async () => {
      if (
        !secondaryFile.value ||
        !localPart.value.id ||
        !localPart.value.set_id
      )
        return;

      const formData = new FormData();
      formData.append('file', secondaryFile.value);
      formData.append('set_id', localPart.value.set_id.toString());
      formData.append('part_id', localPart.value.id.toString());
      formData.append('secondary', '1');

      try {
        const { data } = await axios.post('/api/upload-set-part', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        localPart.value.secondary_content = data.secondary_content;

        await nextTick();
        showToast('Imagem secundária enviada com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao enviar imagem secundária', 'error');
      }
    };

    const onSecondaryFileDelete = () => {
      localPart.value.secondary_content = '';
      secondaryFile.value = null;
    };

    const isPdf = (filePath: string): boolean => {
      return filePath.toLowerCase().endsWith('.pdf');
    };

    const isFirstPart = computed(() => {
      return props.currentPartIndex <= 0;
    });

    const isLastPart = computed(() => {
      return props.currentPartIndex >= props.allParts.length - 1;
    });

    const navigateToPrevious = () => {
      if (!isFirstPart.value) {
        const newIndex = props.currentPartIndex - 1;
        const targetPart = props.allParts[newIndex];
        emit('navigate-to-part', targetPart, newIndex);
      }
    };

    const navigateToNext = () => {
      if (!isLastPart.value) {
        const newIndex = props.currentPartIndex + 1;
        const targetPart = props.allParts[newIndex];
        emit('navigate-to-part', targetPart, newIndex);
      }
    };

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
      lockedValues,
      secondaryFile,
      show: props.show,
      isFirstPart,
      isLastPart,
      roundValue,
      onTypeChange,
      fillMaterialDetails,
      fillSheetDetails,
      fillBarDetails,
      fillComponentDetails,
      loadDataBasedOnPartType,
      savePart,
      closeDialog,
      calculateProperties,
      onUnitValueChange,
      onFinalValueChange,
      onUnitNetWeightChange,
      onUnitGrossWeightChange,
      onNetWeightChange,
      onGrossWeightChange,
      recalculatePart,
      getPartImageUrl: props.getPartImageUrl,
      onSecondaryFileChange,
      onSecondaryFileDelete,
      isPdf,
      navigateToPrevious,
      navigateToNext,
    };
  },
});
</script>

<style>
.part-form-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.navigation-arrow {
  position: fixed;
  top: 50%;
  transform: translateY(-50%);
  z-index: 9999;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.navigation-arrow-left {
  left: 30px;
}

.navigation-arrow-right {
  right: 30px;
}

.dense-form .v-row {
  margin-bottom: -20px;
}

.secondary-preview {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #eee;
  display: inline-block;
}

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  margin-left: 12px;
  display: flex;
  align-items: center;
}

.close-btn svg {
  width: 24px;
  height: 24px;
  color: #555;
  transition: color 0.2s;
}

.close-btn:hover svg {
  color: white;
}
</style>
