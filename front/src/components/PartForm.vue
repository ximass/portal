<template>
  <v-dialog 
    :model-value="show"
    @update:model-value="(value) => { if (!value) closeDialog(); }"
    width="80vw"
    height="80vh"
  >
    <v-card>
      <v-card-title>{{ part?.title || 'Detalhes da Part' }}</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="8">
            <v-responsive max-height="60vh" max-width="60vw" class="overflow-auto">
              <v-img
                v-if="part?.content"
                :src="getPartImageUrl(part.content)"
                contain
                max-width="100%"
              />
              <div v-else>Sem imagem para exibir</div>
            </v-responsive>
          </v-col>
          <v-col cols="4" class="d-flex flex-column">
            <v-textarea
              label="Descrição"
              placeholder="Descrição do part"
              v-model="description"
            />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn color="white" variant='flat' @click="closeDialog">Fechar</v-btn>
        <v-btn color="primary" variant='flat' @click="savePart">Salvar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, PropType, ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default defineComponent({
  name: 'PartForm',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    part: {
      type: Object as PropType<{ id: string, set_id: string, title: string; content: string }>,
      default: null
    },
    getPartImageUrl: {
      type: Function as PropType<(part: any) => string>,
      required: true
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const localPart = ref({ ...props.part });
    const description = ref('');

    const { showToast } = useToast();

    watch(
      () => props.part,
      (newVal) => {
        if (newVal) {
          localPart.value = { ...newVal };

          //TODO: DINAMIZAR
          description.value = '';
        }
      },
      { deep: true, immediate: true }
    );

    const closeDialog = () => {
      emit('close');
    };

    const savePart = async () => {
      if (!localPart.value.id || !localPart.value.set_id) return;

      try {
        //localPart.value.content = description.value;

        await axios.put(`/api/sets/${localPart.value.set_id}/parts/${localPart.value.id}`, {
          title: localPart.value.title,
          content: localPart.value.content
        });

        emit('close');
      } catch (error) {
        showToast('Erro ao salvar a peça', 'error');
      }
    };

    return {
      localPart,
      description,
      show: props.show,
      closeDialog,
      savePart
    };
  }
});
</script>