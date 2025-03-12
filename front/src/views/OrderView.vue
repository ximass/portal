<template>
  <v-container>
    <v-card class="mb-4">
      <v-card-title>Pedido</v-card-title>
      <v-card-text>
        <v-form ref="formRef">
          <v-row>
            <v-col cols="4" md="4" sm="12">
              <v-text-field label="Valor final" placeholder="Digite o valor do valor final" v-model="form.final_value" />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

    <v-card v-if="!isNew">
      <v-card-title>Peças</v-card-title>
      <v-card-text>
        <v-file-input
          v-model="fileList"
          multiple
          clearable
          label="Selecione arquivos"
          show-size
          counter
        />
        <div class="images-container">
          <v-row class="d-flex flex-row" dense>
            <v-col
              v-for="(image, revIndex) in images.slice().reverse()"
              :key="revIndex"
              cols="auto"
              class="pa-2"
            >
              <div class="image-preview">
                <v-img :src="image.url" width="150" height="150" contain>
                  <template #error>
                    <div
                      style="width:150px;height:150px;display:flex;align-items:center;justify-content:center;background-color:#f0f0f0"
                    >
                      <v-icon large color="grey lighten-1">mdi-file</v-icon>
                    </div>
                  </template>
                </v-img>
                <div class="counter">{{ revIndex + 1 }}</div>
                <div class="overlay">
                  <span class="overlay-text">{{ image.name }}</span>
                  <v-icon color="white" class="delete-icon" @click="deleteImage(images.length - 1 - revIndex)">
                    mdi-delete
                  </v-icon>
                </div>
              </div>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>

    <v-row style="display:flex; justify-content:end; padding: 16px;">
      <v-btn color="primary" @click="saveOrder">Salvar</v-btn>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

export default defineComponent({
  name: 'Orders',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');

    const form = ref({
      final_value: '',
    });

    onMounted(async () => {
      if (!isNew.value && route.params.id) {
        try {
          const { data } = await axios.get(`/api/orders/${route.params.id}`);
          form.value.final_value = data.final_value;
        } catch (error) {
          console.error(error);
        }
      }
    });

    const saveOrder = async () => {
      try {
        if (isNew.value) {
          await axios.post('/api/orders', { final_value: form.value.final_value });
        } else {
          await axios.put(`/api/orders/${route.params.id}`, { final_value: form.value.final_value });
        }

        router.push({ name: 'OrdersView' });
      } catch (error) {
        console.error(error);
      }
    };

    const images = ref<{ name: string; url: string }[]>([]);
    const fileList = ref<File[] | null>(null);

    const handleFileUpload = async () => {
      const files = fileList.value;
      if (files && files.length) {
        for (const file of files) {
          const formData = new FormData();
          formData.append('file', file);
          try {
            const response = await axios.post('/api/upload-order-part', formData, {
              headers: { 'Content-Type': 'multipart/form-data' },
            });
            images.value.push({
              name: file.name,
              url: response.data.file_path,
            });
          } catch (error) {
            console.error('File upload error:', error);
          }
        }
      }
      fileList.value = null;
    };

    const deleteImage = (index: number) => {
      images.value.splice(index, 1);
    };

    watch(fileList, (newFiles) => {
      if (newFiles && newFiles.length) {
        handleFileUpload();
      }
    });

    return {
      isNew,
      form,
      images,
      fileList,
      handleFileUpload,
      deleteImage,
      saveOrder,
    };
  },
});
</script>

<style scoped>
.v-card {
  margin-bottom: 16px;
}
.image-preview {
  position: relative;
  width: 150px;
  height: 150px;
  border: 2px solid #f0f0f0;
  border-radius: 4px;
}
.counter {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #1976d2;
  color: white;
  padding: 2px 6px;
  border-radius: 12px;
  font-size: 12px;
  z-index: 2;
}
.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2px 4px;
  min-height: 40px;
}
.overlay-text {
  font-size: 12px;
  color: white;
}
.delete-icon {
  cursor: pointer;
}

.images-container {
  max-height: calc(150px * 3 + 16px);
  overflow-y: auto;
  padding: 8px;
}

.images-container::-webkit-scrollbar {
  width: 8px;
}
.images-container::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.4);
  border-radius: 4px;
}
</style>