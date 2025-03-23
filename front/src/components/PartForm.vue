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
          <v-col cols="6">
            <!-- Card 1: Apenas Título e Material Selector -->
            <v-card class="mb-4 pa-4">
              <v-select
                label="Tipo de material"
                :items="materialsType"
                item-title="name"
                item-value="value"
                v-model="selectedMaterialType"
                @update:modelValue="onMaterialTypeChange"
                :disabled="isMaterialTypeDisabled"
                required
              />
            </v-card>
  
            <!-- Card 2: Extra Fields liberados após seleção do material -->
            <v-card v-if="showExtraFields" class="pa-4">
              <v-row>
                <v-col cols="12">
                  <v-select label="Material" :items="materials" item-title="name" item-value="id" v-model="selectedMaterial" required />
                </v-col>
              </v-row>
              <v-row v-if="selectedMaterialObject && (selectedMaterialObject.value === 'sheet' || selectedMaterialObject.value === 'bar')">
                <v-col cols="4">
                  <v-text-field label="Largura" v-model="localPart.width" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Comprimento" v-model="localPart.length" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Perda" v-model="localPart.loss" type="number" required />
                </v-col>
              </v-row>
              <v-row v-else-if="selectedMaterialObject && selectedMaterialObject.value === 'component'">
                <v-col cols="12">
                  <v-text-field label="Markup" v-model="localPart.markup" type="number" required />
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="4">
                  <v-text-field label="Quantidade" v-model="localPart.quantity" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Peso líquido unitário" v-model="localPart.unit_net_weight" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Peso bruto unitário" v-model="localPart.unit_gross_weight" type="number" required />
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="4">
                  <v-text-field label="Peso líquido" v-model="localPart.net_weight" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Peso bruto" v-model="localPart.net_gross_weight" type="number" required />
                </v-col>
                <v-col cols="4">
                  <v-text-field label="Valor unitário" v-model="localPart.unit_value" type="number" required />
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="4">
                  <v-text-field label="Valor final" v-model="localPart.final_value" type="number" required />
                </v-col>
              </v-row>
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
import { defineComponent, PropType, ref, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { Part, MaterialType } from '@/types';

export default defineComponent({
  name: 'PartForm',
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
    const localPart = ref({ ...props.part });
    const selectedMaterialType = ref<string | null>(null);
    const selectedMaterial = ref<any>(null);
    const materialsType = ref<MaterialType[]>([]);
    const materials = ref<any[]>([]);
    const showExtraFields = ref(false);
    const isMaterialTypeDisabled = ref(false);
    const { showToast } = useToast();

    const fetchMaterialsTypes = async () => {
      try {
        const { data } = await axios.get('/api/materials/types');
        materialsType.value = data;
      } catch (error) {
        showToast('Erro ao buscar tipos de materiais', 'error');
      }
    };

    // Fetch materials by the selected type
    const fetchMaterialsByType = async () => {
      if (!selectedMaterialType.value) return;

      try {
        const { data } = await axios.get(`/api/materials?type=${selectedMaterialType.value}`);
        materials.value = data;
      } catch (error) {
        showToast('Erro ao buscar materiais', 'error');
      }
    };

    // Fetch a material by id to retrieve its type information
    const fetchMaterialById = async (id: string) => {
      try {
        const { data } = await axios.get(`/api/materials/${id}`);

        return data;
      } catch (error) {
        showToast('Erro ao buscar o material', 'error');
        return null;
      }
    };

    onMounted(() => {
      fetchMaterialsTypes();
    });

    // Computed to get the selected material type object
    const selectedMaterialObject = computed(() => {
      return materialsType.value.find(m => m.value === selectedMaterialType.value);
    });

    // Watch to load part data when props.part is updated
    watch(
      () => props.part,
      (newVal) => {
        if (newVal) {
          localPart.value = { ...newVal };
          
          // Load material data if exists
          if ((newVal as any).material_id) {
            selectedMaterial.value = (newVal as any).material_id;
            // Fetch the material by its id to obtain its type
            fetchMaterialById(selectedMaterial.value).then(material => {
              if (material) {
                selectedMaterialType.value = material.type;
                isMaterialTypeDisabled.value = true;
                showExtraFields.value = true;

                fetchMaterialsByType();
              }
            });
          } else {
            selectedMaterial.value = null;
            // If material_id doesn't exist, allow material type to be chosen
            selectedMaterialType.value = (newVal as any).material_type || null;
            isMaterialTypeDisabled.value = false;
            showExtraFields.value = !!selectedMaterialType.value;

            if (selectedMaterialType.value) {
              fetchMaterialsByType();
            }
          }
        }
      },
      { deep: true, immediate: true }
    );

    const onMaterialTypeChange = async (val: string) => {
      if (!selectedMaterialType.value) return;
      showExtraFields.value = true;
      await fetchMaterialsByType();
    };

    // Save part with form data.
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
        showToast('Erro ao salvar a peça', 'error');
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