<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
    /** Whether the modal is visible */
    show: { type: Boolean, default: false },
    /** Source image (data URL or blob URL) */
    imageSrc: { type: String, default: null },
    /** Aspect ratio for cropping: 1 = square (logo), 3 = landscape (portada) */
    aspectRatio: { type: Number, default: 1 },
    /** Output width in pixels */
    outputWidth: { type: Number, default: 400 },
    /** Output height in pixels */
    outputHeight: { type: Number, default: 400 },
    /** Title shown in the modal header */
    title: { type: String, default: 'Recortar imagen' },
    /** Whether to show a circular mask (for logos) */
    circular: { type: Boolean, default: false },
});

const emit = defineEmits(['cropped', 'cancel']);

const imageEl = ref(null);
let cropper = null;

const initCropper = () => {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    if (!imageEl.value) return;

    cropper = new Cropper(imageEl.value, {
        aspectRatio: props.aspectRatio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.9,
        responsive: true,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
        background: true,
    });
};

watch(() => props.show, (val) => {
    if (val && props.imageSrc) {
        nextTick(() => setTimeout(initCropper, 100));
    } else if (!val && cropper) {
        cropper.destroy();
        cropper = null;
    }
});

onBeforeUnmount(() => {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

const rotateLeft  = () => cropper?.rotate(-90);
const rotateRight = () => cropper?.rotate(90);
const zoomIn      = () => cropper?.zoom(0.1);
const zoomOut     = () => cropper?.zoom(-0.1);
const reset       = () => cropper?.reset();

const confirm = () => {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        width: props.outputWidth,
        height: props.outputHeight,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob((blob) => {
        if (blob) {
            const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
            const preview = URL.createObjectURL(blob);
            emit('cropped', { file, preview });
        }
    }, 'image/jpeg', 0.92);
};

const cancel = () => emit('cancel');
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" @click.self="cancel">
                <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                        <button type="button" @click="cancel" class="rounded-lg p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Cropper area -->
                    <div class="relative bg-gray-900" :class="circular ? 'cropper-circular' : ''">
                        <div class="mx-auto" style="max-height: 420px;">
                            <img
                                v-if="imageSrc"
                                ref="imageEl"
                                :src="imageSrc"
                                alt="Imagen a recortar"
                                class="block max-w-full"
                                style="max-height: 420px;"
                            />
                        </div>
                    </div>

                    <!-- Toolbar -->
                    <div class="flex items-center justify-center gap-2 border-t bg-gray-50 px-6 py-3">
                        <button type="button" @click="rotateLeft" class="rounded-lg bg-white p-2 text-gray-600 shadow-sm ring-1 ring-gray-200 hover:bg-gray-100" title="Rotar izquierda">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4m-4 4l4 4" />
                            </svg>
                        </button>
                        <button type="button" @click="rotateRight" class="rounded-lg bg-white p-2 text-gray-600 shadow-sm ring-1 ring-gray-200 hover:bg-gray-100" title="Rotar derecha">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4" />
                            </svg>
                        </button>
                        <div class="mx-1 h-6 w-px bg-gray-200"></div>
                        <button type="button" @click="zoomIn" class="rounded-lg bg-white p-2 text-gray-600 shadow-sm ring-1 ring-gray-200 hover:bg-gray-100" title="Zoom +">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                        </button>
                        <button type="button" @click="zoomOut" class="rounded-lg bg-white p-2 text-gray-600 shadow-sm ring-1 ring-gray-200 hover:bg-gray-100" title="Zoom -">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                            </svg>
                        </button>
                        <div class="mx-1 h-6 w-px bg-gray-200"></div>
                        <button type="button" @click="reset" class="rounded-lg bg-white p-2 text-gray-600 shadow-sm ring-1 ring-gray-200 hover:bg-gray-100" title="Restablecer">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 border-t px-6 py-4">
                        <button type="button" @click="cancel" class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" @click="confirm" class="rounded-lg bg-primary-600 px-5 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                            Aplicar recorte
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Circular crop mask for logos */
.cropper-circular :deep(.cropper-view-box),
.cropper-circular :deep(.cropper-face) {
    border-radius: 50%;
}
</style>
