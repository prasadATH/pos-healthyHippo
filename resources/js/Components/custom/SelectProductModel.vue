<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="closeModal">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-white border-1 border-gray-600 rounded-[20px] shadow-xl w-5/6 p-6 text-center min-h-[600px]"
          >
            <div class="flex flex-col items-center justify-start">
              <div class="w-full flex">
                <div class="w-full py-12 space-y-16">
                  <div
                    class="flex items-center justify-between w-full space-x-4"
                  >
                    <!-- Input Section -->
                    <div class="w-1/6">
                      <input
                        v-model="search"
                        @input="() => fetchProducts()"
                        type="text"
                        placeholder="Search ..."
                        class="w-full custom-input"
                      />
                    </div>

                    <!-- Top Categories Section -->
                    <div class="w-5/6 flex items-center justify-end space-x-4">
                      <p class="text-2xl">Top Categories:</p>
                      <div
                        v-for="category in parentCategories"
                        :key="category.id"
                        class="cursor-pointer"
                        @click="selectParentCategory(category)"
                      >
                        <p
                          :class="{
                            'text-2xl text-blue-600':
                              selectedParentCategory?.id !== category.id,
                            'text-2xl text-red-600':
                              selectedParentCategory?.id === category.id,
                          }"
                        >
                          {{ category.name }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex w-full space-x-4">
                <select
                  v-model="selectedCategory"
                  @change="() => fetchProducts()"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                >
                  <option value="">Filter by Category</option>
                  <option
                    v-for="category in allcategoriesFiltered"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{
                      category.hierarchy_string
                        ? category.hierarchy_string + " ----> " + category.name
                        : category.name
                    }}
                  </option>
                </select>

                <select
                  v-model="stockStatus"
                  @change="() => fetchProducts()"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                >
                  <option value="">Filter by Stock</option>
                  <option value="in">In Stock</option>
                  <option value="out">Out of Stock</option>
                </select>
                <select
                  v-model="sort"
                  @change="() => fetchProducts()"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                >
                  <option value="">Filter by Price</option>
                  <option value="asc">Ascending</option>
                  <option value="desc">Descending</option>
                </select>

                <select
                  v-model="color"
                  @change="() => fetchProducts()"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                >
                  <option value="">Filter by Base</option>
                  <option
                    v-for="colorOption in colors"
                    :key="colorOption.id"
                    :value="colorOption.name"
                  >
                    {{ colorOption.name }}
                  </option>
                </select>
                <select
                  v-model="size"
                  @change="() => fetchProducts()"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                >
                  <option value="">Filter by Size</option>
                  <option
                    v-for="sizeOption in sizes"
                    :key="sizeOption.id"
                    :value="sizeOption.name"
                  >
                    {{ sizeOption.name }}
                  </option>
                </select>
                <span
                  @click="resetFilters"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select"
                >
                  Reset
                </span>
              </div>
            </div>

            <div class="mt-8">
              <template v-if="loading">
                <div class="col-span-4 text-center text-gray-500">
                  <p class="text-center text-red-500 text-[17px]">Loading...</p>
                </div>
              </template>
              <template v-else>
                <template v-if="products.data.length > 0">
                  <div class="overflow-x-auto">
                    <div class="grid grid-flow-col auto-cols-[25%] gap-8">
                      <div
                        v-for="product in products.data.filter(
                          (product) => !(hidePromotions && product.is_promotion)
                        )"
                        :key="product.id"
                        @click="
                          product.stock_quantity > 0 && selectProduct(product)
                        "
                        :class="[
                          'space-y-4 text-white transition-transform duration-300 transform bg-black border-4 shadow-lg',
                          product.stock_quantity > 0
                            ? selectedProducts.find((p) => p.id === product.id)
                              ? 'border-green-600 hover:-translate-y-4 cursor-pointer'
                              : 'border-black hover:-translate-y-4 cursor-pointer'
                            : 'border-red-600 cursor-not-allowed',
                        ]"
                      >
                        <div>
                          <img
                            :src="
                              product.image
                                ? `/${product.image}`
                                : '/images/placeholder.jpg'
                            "
                            alt="Product Image"
                            class="object-cover w-full h-64"
                          />
                        </div>
                        <div class="px-2 py-4 space-y-4">
                          <div
                            class="flex items-start space-x-3 justify-between text-[11px] font-bold tracking-wide"
                          >
                            <p class="text-justify">
                              {{ product.name || "N/A" }}
                            </p>
                            <p
                              class="px-3 text-white bg-green-700 py-2 rounded-full flex items-center"
                            >
                              {{ product.selling_price || "N/A" }}
                            </p>
                          </div>
                          <div
                            class="flex justify-center space-x-2 items-start w-full"
                          >
                            <div class="flex space-x-1 text-gray-400">
                              <p class="font-bold">Color:</p>
                              <p>{{ product.color?.name || "N/A" }}</p>
                            </div>
                            <div class="flex space-x-1 text-gray-400">
                              <p class="font-bold">Size:</p>
                              <p>
                                {{ product.size?.name || "N/A" }}
                              </p>
                            </div>
                          </div>
                          <!-- <div
                            class="flex items-center justify-center w-full space-x-4"
                          >
                            <p
                              class="flex items-center space-x-2 text-justify text-gray-400"
                            >
                              Supplier :
                              <b>
                                &nbsp; {{ product.supplier?.name || "N/A" }}
                              </b>
                            </p>
                          </div> -->
                          <div class="flex items-center justify-between">
                            <div>
                              <p
                                v-if="product.stock_quantity > 0"
                                class="text-xl font-bold tracking-wider text-green-500"
                              >
                                <i class="ri-checkbox-blank-circle-fill"></i> In
                                Stock
                              </p>
                              <p
                                v-else
                                class="text-xl font-bold tracking-wider text-red-500"
                              >
                                <i class="ri-checkbox-blank-circle-fill"></i>
                                Out of Stock
                              </p>
                            </div>
                            <div v-if="product.is_promotion">
                              <p
                                @click.stop="viewPromotion(product)"
                                class="text-xl font-bold tracking-wider text-orange-300 border border-orange-300 rounded-3xl px-4 py-2"
                              >
                                Promotion Items
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between mt-6">
                    <div class="pagination flex space-x-4">
                      <button
                        @click="fetchPage(products.prev_page_url)"
                        :disabled="!products.prev_page_url"
                        class="px-4 py-2 text-[15px] text-white bg-blue-500 rounded-md shadow-md hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                      >
                        Previous
                      </button>
                      <button
                        @click="fetchPage(products.next_page_url)"
                        :disabled="!products.next_page_url"
                        class="px-4 py-2 text-[15px] text-white bg-blue-500 rounded-md shadow-md hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                      >
                        Next
                      </button>
                    </div>
                    <button
                      class="px-6 py-2 text-[15px] text-white bg-blue-600 rounded hover:bg-blue-700"
                      @click.prevent="closeModal(true)"
                    >
                      Import
                    </button>
                  </div>
                </template>
                <template v-else>
                  <div class="col-span-4 text-center text-gray-500">
                    <p class="text-center text-red-500 text-[17px]">
                      No Products available
                    </p>
                  </div>
                </template>
              </template>
            </div>

            <PromotionItemModal
              :open="itemModalOpen"
              @update:open="itemModalOpen = $event"
              :product="selectedPromotion"
            />
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { debounce } from "lodash";
import PromotionItemModal from "@/Components/custom/PromotionItemModal.vue";

const itemModalOpen = ref(false);

const products = ref({});
// const categories = ref([]); // If you need to fetch categories for filtering
const loading = ref(false);
const search = ref("");
const selectedCategory = ref("");
const stockStatus = ref("");
const sort = ref("");
const color = ref("");
const size = ref("");
const selectedParentCategory = ref(null);
const parentCategories = ref([]);
const allcategoriesFiltered = ref(allcategories);

const selectedPromotion = ref(null);

const selectedProducts = ref([]);

const selectProduct = (product) => {
  const index = selectedProducts.value.findIndex((p) => p.id === product.id);
  if (index === -1) {
    // Product does not exist, add it to the array
    selectedProducts.value.push(product);
    // console.log("Product added:", product);
  } else {
    // Product exists, remove it from the array
    selectedProducts.value.splice(index, 1);
    // console.log("Product removed:", product);
  }
};

const viewPromotion = (product) => {
  selectedPromotion.value = product;
  itemModalOpen.value = true;
};

const resetFilters = () => {
  search.value = "";
  selectedCategory.value = "";
  stockStatus.value = "";
  sort.value = "";
  color.value = "";
  size.value = "";

  fetchProducts();
};

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open", "selected-products"]);

// Props for the modal
const { open, allcategories, colors, sizes, hidePromotions } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  allcategories: Array,
  colors: Array,
  sizes: Array,
  hidePromotions: {
    type: Boolean, // The type of the prop
    required: false, // Optional prop
    default: false, // Default value if not provided
  },
});

// Form for handling deletion
const form = useForm({});

// Close modal handler to emit the state change properly
const closeModal = (triggerImport = false) => {
  playClickSound();
  emit("update:open", false);

  if (triggerImport) {
    emit("selected-products", selectedProducts.value);
  }
  selectedProducts.value = [];
};
const fetchProducts = async (url = "/api/products") => {
  loading.value = true;
  try {
    const response = await axios.post(url, {
      search: search.value,
      selectedCategory: selectedCategory.value,
      stockStatus: stockStatus.value,
      sort: sort.value,
      color: color.value,
      size: size.value,
    });
    products.value = response.data.products;
  } catch (error) {
    console.error("Error fetching products:", error);
  } finally {
    loading.value = false;
  }
};

const fetchParentCategories = async (url = "/api/top-categories") => {
  try {
    const response = await axios.post(url);
    parentCategories.value = response.data.categories;
  } catch (error) {
    console.error("Error fetching categories:", error);
  } finally {
    // loading.value = false;
  }
};

const fetchPage = (url) => {
  if (url) {
    fetchProducts(url);
  }
};

const selectParentCategory = (category) => {
  selectedParentCategory.value = category;
  allcategoriesFiltered.value = [category, ...(category.children || [])];

  console.log(allcategoriesFiltered.value);
  selectedCategory.value = category.id;
  fetchProducts();
};

// Fetch products on mount
fetchProducts();
fetchParentCategories();
</script>
