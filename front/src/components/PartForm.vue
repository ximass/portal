<template>
  <v-dialog :model-value="show" @update:model-value="(value) => { if (!value) closeDialog(); }" width="80vw"
    height="90vh">
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
            <!-- Card 1: Apenas Título e Material Selector -->
            <v-row dense>
              <v-col cols="12">
                <v-select label="Tipo de material" :items="materialsType" item-title="name" item-value="value"
                  v-model="selectedMaterialType" @update:modelValue="onMaterialTypeChange"
                  :disabled="isMaterialTypeDisabled" required density="compact" />
              </v-col>
            </v-row>

            <!-- Card 2: Extra Fields liberados após seleção do material -->
            <v-card v-if="showExtraFields" class="pa-4">
              <v-row dense>
                <v-col cols="12">
                  <v-select label="Material" :items="materials" item-title="name" item-value="id"
                    v-model="selectedMaterial" required density="compact" />
                </v-col>
              </v-row>
              <v-row dense
                v-if="selectedMaterialObject && (selectedMaterialObject.value === 'sheet' || selectedMaterialObject.value === 'bar')">
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Largura" v-model="localPart.width" type="number" required density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Comprimento" v-model="localPart.length" type="number" required
                    density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Perda" v-model="localPart.loss" type="number" required density="compact" />
                </v-col>
              </v-row>
              <v-row dense v-else-if="selectedMaterialObject && selectedMaterialObject.value === 'component'">
                <v-col cols="12">
                  <v-text-field label="Markup" v-model="localPart.markup" type="number" required density="compact" />
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Quantidade" v-model="localPart.quantity" type="number" required
                    density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso líquido unitário" v-model="localPart.unit_net_weight" type="number" required
                    density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso bruto unitário" v-model="localPart.unit_gross_weight" type="number" required
                    density="compact" />
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso líquido" v-model="localPart.net_weight" type="number" required
                    density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Peso bruto" v-model="localPart.gross_weight" type="number" required
                    density="compact" />
                </v-col>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Valor unitário" v-model="localPart.unit_value" type="number" required
                    density="compact" />
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="12" md="4" small="6">
                  <v-text-field label="Valor final" v-model="localPart.final_value" type="number" required
                    density="compact" />
                </v-col>
              </v-row>

              <ProcessMultiField v-model="localPart.processes" />

            </v-card>
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
import { defineComponent, PropType, ref, watch, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { Part, MaterialType, Material, Process } from '@/types/types';
import ProcessMultiField from '@/components/ProcessMultiField.vue';

export default defineComponent({
  name: 'PartForm',
  components: {
    ProcessMultiField
  },
  props: {
    show: {
      type: Boolean,
      required: true
    },
    part: {
      type: Object as PropType<Part>,
      default: null
    },
    getPartImageUrl: {
      type: Function as PropType<(part: any) => string>,
      required: true
    }
  },
  emits: ['part-saved', 'close'],
  setup(props, { emit }) {
    const { showToast } = useToast();

    // Flag to avoid triggering material change during initialization.
    const isInitialLoad = ref(true);

    // Estado do formulário (inicializado com a peça ou com valores default)
    const localPart = ref<Part>(
      props.part ? { ...props.part } : {
        id: '',
        set_id: '',
        title: '',
        content: '',
        material_id: '',
        quantity: 0,
        unit_net_weight: 0,
        unit_gross_weight: 0,
        net_weight: 0,
        gross_weight: 0,
        unit_value: 0,
        final_value: 0,
        width: 0,
        length: 0,
        loss: 0,
        markup: 0,
        processes: []
      }
    );

    // O v-select de Material trabalha apenas com o id
    const selectedMaterial = ref<number | null>(null);
    const selectedMaterialType = ref<string | null>(null);
    const currentMaterial = ref<Material | null>(null);
    const materialsType = ref<MaterialType[]>([]);
    const materials = ref<Material[]>([]);
    const showExtraFields = ref(false);
    const isMaterialTypeDisabled = ref(false);

    const fetchMaterialsTypes = async () => {
      try {
        const { data } = await axios.get('/api/materials/types');
        materialsType.value = data;
      } catch (error) {
        showToast('Erro ao buscar tipos de materiais', 'error');
      }
    };

    const fetchMaterialsByType = async () => {
      if (!selectedMaterialType.value) return;
      try {
        const { data } = await axios.get(`/api/materials?type=${selectedMaterialType.value}`);
        materials.value = data;
      } catch (error) {
        showToast('Erro ao buscar materiais', 'error');
      }
    };

    // Busca o material completo dado seu id
    const fetchMaterialById = async (id: string): Promise<Material | null> => {
      try {
        const { data } = await axios.get(`/api/materials/${id}`);
        return data as Material;
      } catch (error) {
        showToast('Erro ao buscar o material', 'error');
        return null;
      }
    };

    // Preenche os campos do formulário conforme o material
    const fillMaterialDetails = async (materialId: number) => {
      const material = await fetchMaterialById(materialId.toString());

      if (!material) return;

      currentMaterial.value = material;
      
      if (material.type === 'sheet' && material.sheet) {
        localPart.value.width = material.sheet.width;
        localPart.value.length = material.sheet.length;
      } else if (material.type === 'bar' && material.bar) {
        localPart.value.width = material.bar.width;
        localPart.value.length = material.bar.length;
      } else if (material.type === 'component' && material.component) {
        localPart.value.markup = material.component.markup;
      }
    };

    const calculateProperties = async () => {
      if (!currentMaterial.value) return;

      try {
        const { data } = await axios.post('/api/set-parts/calculateProperties', {
          part: {
            quantity: localPart.value.quantity,
            loss: localPart.value.loss
          },
          material: currentMaterial.value
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

    onMounted(() => {
      fetchMaterialsTypes();
    });

    // Computed para obter o objeto do tipo de material selecionado
    const selectedMaterialObject = computed(() =>
      materialsType.value.find(m => m.value === selectedMaterialType.value)
    );

    watch(
      () => [
          localPart.value.quantity,
          localPart.value.unit_net_weight,
          localPart.value.unit_gross_weight,
          localPart.value.loss
      ],
      () => {
        calculateProperties();
      }
    );

    // Watch que inicializa o formulário quando a prop é atualizada
    watch(
      () => props.part,
      (newVal) => {
        if (newVal) {
          localPart.value = { ...newVal };
          if (newVal.material_id) {
            selectedMaterial.value = Number(newVal.material_id);
            fetchMaterialById(newVal.material_id).then(material => {
              if (material) {
                selectedMaterialType.value = material.type;
                currentMaterial.value = material;
                isMaterialTypeDisabled.value = true;
                showExtraFields.value = true;
                fetchMaterialsByType();
                fillMaterialDetails(material.id);
                // Após a atualização inicial, liberar o watcher para alterações futuras
                nextTick(() => {
                  isInitialLoad.value = false;
                });
              }
            });
          } else {
            selectedMaterial.value = null;
            selectedMaterialType.value = (newVal as any).material_type || null;
            isMaterialTypeDisabled.value = false;
            showExtraFields.value = !!selectedMaterialType.value;
            if (selectedMaterialType.value) {
              fetchMaterialsByType();
            }
            nextTick(() => {
              isInitialLoad.value = false;
            });
          }
        }
      },
      { deep: true, immediate: true }
    );

    // Quando o usuário muda o material, preenche automaticamente os campos (apenas após a inicialização)
    watch(selectedMaterial, (newVal) => {
      if (isInitialLoad.value) return;

      if (newVal) {
        fillMaterialDetails(newVal);
      }
    });

    const onMaterialTypeChange = async () => {
      if (!selectedMaterialType.value) return;
      showExtraFields.value = true;

      await fetchMaterialsByType();

      // Limpa o material selecionado ao trocar o tipo
      selectedMaterial.value = null;
    };

    const savePart = async () => {
      if (!localPart.value.id || !localPart.value.set_id) return;
      try {
        await axios.put(`/api/sets/${localPart.value.set_id}/parts/${localPart.value.id}`, {
          ...localPart.value,
          material_id: selectedMaterial.value,
          material_type: selectedMaterialType.value
        });
        emit('part-saved', localPart.value);
        emit('close');
      } catch (error) {
        showToast('Erro ao salvar a peça: ' + error, 'error');
      }
    };

    const closeDialog = () => {
      emit('close');
    };

    return {
      localPart,
      selectedMaterialType,
      selectedMaterial,
      selectedMaterialObject,
      materialsType,
      materials,
      showExtraFields,
      isMaterialTypeDisabled,
      closeDialog,
      savePart,
      onMaterialTypeChange,
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