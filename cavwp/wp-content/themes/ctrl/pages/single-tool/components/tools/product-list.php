<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolProductList', function() {
         return {
            list: this.$persist([{
               name: 'Arroz',
               lowestPrice: 20,
               tags: ['2'],
               quantity: 1,
               sizeValue: 5,
               sizeUnit: 'kg',
               repeatValue: 1,
               repeatUnit: 'months',
               lastCheck: 0,
               nextCheck: 0,
            }, {
               name: 'Feijão',
               lowestPrice: 10,
               tags: ['1'],
               quantity: 1,
               sizeValue: 1,
               sizeUnit: 'kg',
               repeatValue: 3,
               repeatUnit: 'weeks',
               lastCheck: 0,
               nextCheck: 0,
            }]).as('tools-product-list-list'),
            tags: this.$persist({
               1: 'Hipermercado',
               2: 'Supermercado'
            }).as('tools-product-list-tags'),
            repeatUnits: {
               days: {
                  singular: 'dia',
                  plural: 'dias',
               },
               weeks: {
                  singular: 'semana',
                  plural: 'semanas',
               },
               months: {
                  singular: 'mês',
                  plural: 'meses',
               },
               years: {
                  singular: 'ano',
                  plural: 'anos',
               },
            },
            sizeUnits: {
               un: 'un',
               kg: 'kg',
               g: 'g',
               l: 'l',
               mg: 'mg',
               ml: 'ml'
            },
            editing: false,
            filter: [],
            currentTag: false,

            init() {
               this.updateFilter()
               this.$watch('list', () => this.updateFilter())
               this.$watch('tags', () => this.updateFilter())

               this.refreshList = this.list
               this.$watch('list', () => this.updateList())
               this.$watch('tags', () => this.updateList())
               this.$watch('currentTag', () => this.updateList())

               this.$watch('editing', (id) => this.setEditing(id))
            },

            updateFilter() {
               const tags = Object.entries(this.tags).map(([tagIndex, tag]) =>
                  ({
                     key: tagIndex,
                     name: tag,
                     count: this.list.filter((product) => product.tags.includes(tagIndex)).length
                  })
               )

               this.filter = tags.filter((tag) => tag.count > 0)
            },

            filterTag(key) {
               if (key === this.currentTag) {
                  this.currentTag = false
                  return
               }

               this.currentTag = key
            },

            updateList() {
               let list = this.list
               if (this.currentTag) {
                  list = list.filter((product) => product.tags.includes(this.currentTag))
               }

               list.sort((a, b) => a.nextCheck - b.nextCheck)

               this.refreshList = list
            },

            setEditing(index) {
               if (index === false) {
                  return
               }

               const {
                  name,
                  lowestPrice,
                  tags,
                  quantity,
                  sizeValue,
                  sizeUnit,
                  repeatValue,
                  repeatUnit
               } = this.list[this.editing]

               document.getElementById('name').value = name
               document.getElementById('lowestPrice').value = lowestPrice
               document.getElementById('quantity').value = quantity
               document.getElementById('sizeValue').value = sizeValue
               document.getElementById('repeatValue').value = repeatValue

               document.getElementById('sizeUnit').value = sizeUnit
               document.getElementById('repeatUnit').value = repeatUnit

               document.getElementsByName('tags').forEach(tagEl => tagEl.checked = false)

               tags.forEach(tag =>
                  document.getElementById(`tag-${tag}`).checked = true
               );
            },

            addProduct(e) {
               const formData = new FormData(e.currentTarget)

               let tags = []
               document.getElementsByName('tags').forEach(tagEl => {
                  if (tagEl.checked) {
                     tags.push(tagEl.value)
                  }
               })

               this.list.push({
                  name: formData.get('name'),
                  lowestPrice: formData.get('lowestPrice'),
                  tags,
                  quantity: formData.get('quantity'),
                  sizeValue: formData.get('sizeValue'),
                  sizeUnit: formData.get('sizeUnit'),
                  repeatValue: formData.get('repeatValue'),
                  repeatUnit: formData.get('repeatUnit'),
                  lastCheck: 0,
                  nextCheck: 0,
               })

               e.currentTarget.reset()
            },

            patchProduct(productIndex, key, value) {
               this.list[productIndex][key] = value
               const {
                  lastCheck,
                  repeatValue,
                  repeatUnit
               } = this.list[productIndex]

               if (!lastCheck) {
                  this.list[productIndex]['nextCheck'] = 0
               } else {
                  if (lastCheck && repeatValue && repeatUnit) {
                     this.list[productIndex]['nextCheck'] = this.$time.plus(lastCheck, repeatValue, repeatUnit)
                  }
               }
            },

            putProduct(e) {
               const formData = new FormData(e.currentTarget)

               this.list[this.editing]['lowestPrice'] = formData.get('lowestPrice')
               this.list[this.editing]['quantity'] = formData.get('quantity')
               this.list[this.editing]['sizeValue'] = formData.get('sizeValue')
               this.list[this.editing]['sizeUnit'] = formData.get('sizeUnit')
               this.list[this.editing]['repeatValue'] = formData.get('repeatValue')
               this.list[this.editing]['repeatUnit'] = formData.get('repeatUnit')

               let tags = []
               document.getElementsByName('tags').forEach(tagEl => {
                  if (tagEl.checked) {
                     tags.push(tagEl.value)
                  }
               })
               this.list[this.editing]['tags'] = tags

               this.patchProduct(this.editing, 'name', formData.get('name'))

               this.editing = false
               e.currentTarget.reset()
            },

            removeProduct(productIndex, fromForm = false) {
               if (!confirm(`Remover ${this.list[productIndex].name} da lista?`)) {
                  return
               }

               this.list.splice(productIndex, 1)

               if (fromForm) {
                  document.getElementById('addProduct').reset()
               }
            },

            addTag() {
               const tagName = prompt('Nova tag', '')

               if (!tagName) {
                  return
               }

               const timestamp = Date.now()
               this.tags[timestamp] = tagName

               this.$nextTick(() => {
                  document.getElementById(`tag-${timestamp}`).checked = true
               })
            },

            removeTag(tagIndex) {
               if (!confirm(`Remover tag ${this.tags[tagIndex]} de todos os produtos?`)) {
                  return
               }

               delete this.tags[tagIndex]

               this.list.forEach(item => {
                  delete item.tags[item.tags.indexOf(tagIndex)]
               })
            },

            remainingDays(productIndex) {
               const {
                  nextCheck
               } = this.list[productIndex]

               if (nextCheck) {
                  const {
                     amount,
                     metric
                  } = this.$time.diff(nextCheck)

                  if (['seconds', 'minutes', 'hours'].includes(metric)) {
                     this.patchProduct(productIndex, 'lastCheck', false)
                  } else {
                     return `Em ${amount} ${this.repeatUnits[metric][amount === 1 ? 'singular' : 'plural']}`
                  }
               }

               return ''
            }
         }
      })
   })
</script>
<div x-data="toolProductList">
   <div class="flex flex-col gap-4">
      <div class="form-item">
         <span class="label">Filtrar</span>
         <ul class="flex flex-wrap items-center gap-1 text-sm sm:text-base">
            <template x-for="{key, name, count} in filter" x-bind:key="key">
               <li>
                  <button class="btn-outline input" type="button" x-bind:class="{'btn': currentTag === key}" x-on:click="filterTag(key)">
                     <span x-text="name"></span>
                     <span class="post-input" x-text="count"></span>
                  </button>
               </li>
            </template>
         </ul>
      </div>

      <ul class="flex flex-col gap-3 text-sm sm:text-lg">
         <template x-for="(item, index) in refreshList" x-bind:key="index">
            <li class="flex items-center gap-1" x-bind:class="{'opacity-75': item.lastCheck}">
               <button class="btn-alt hidden sm:inline" type="button" x-on:click.prevent="removeProduct(index)">
                  <i class="ri-delete-bin-2-line text-lg sm:text-2xl"></i>
               </button>
               <button class="btn-alt" type="button" x-on:click.prevent="editing = index">
                  <i class="ri-edit-box-line text-lg sm:text-2xl"></i>
               </button>
               <div class="flex gap-1 sm:gap-2 items-baseline grow min-w-0">
                  <span class="font-semibold text-xs sm:text-base text-neutral-500" x-text="`${item.quantity}x`"></span>
                  <span class="text-sm sm:text-xl truncate" x-text="item.name"></span>
                  <span class="text-xs sm:text-base text-neutral-500" x-show="item.sizeValue" x-text="`${item.sizeValue}${item.sizeUnit}`" x-cloak></span>
                  </span>
               </div>
               <div class="form-item !w-22 sm:!w-40 flex-none">
                  <div class="input">
                     <span class="pre-input">R$</span>
                     <input class="text-center font-semibold sm:text-lg" type="number" step="0.01" x-bind:value="item.lowestPrice" maxlength="4" x-on:input.prevent="patchProduct(index,'lowestPrice',$el.value)" />
                  </div>
               </div>

               <button class="btn-alt" type="button" x-on:click.prevent="patchProduct(index, 'lastCheck', 0)" x-show="item.lastCheck && !item.nextCheck" x-cloak>
                  <i class="ri-arrow-up-line text-lg sm:text-2xl"></i>
               </button>

               <button class="btn-alt max-w-16 sm:max-w-20 leading-1" type="button" x-on:click.prevent="patchProduct(index, 'lastCheck', Date.now())" x-show="!item.lastCheck || item.nextCheck" x-cloak>
                  <i class="ri-shopping-cart-line text-lg sm:text-2xl" x-show="!item.lastCheck" x-cloak></i>
                  <span class="text-xs" x-text="remainingDays(index)"></span>
               </button>
            </li>
         </template>
      </ul>
      <hr class="border-neutral-600" />
      <form id="addProduct" class="flex flex-col gap-3 p-4" x-on:submit.prevent="editing !== false ? putProduct : addProduct">
         <h2 class="sr-only" x-text="editing !== false ? 'Editar produto' : 'Adicionar produto'"></h2>

         <div class="flex gap-1 w-full">
            <div class="form-item !w-23">
               <label for="quantity">Quantidade</label>
               <input id="quantity" name="quantity" type="number" value="1" step="1" placeholder="Quantidade" />
            </div>

            <div class="form-item grow">
               <label for="name">Produto*</label>
               <input id="name" name="name" type="text" placeholder="Produto" required />
            </div>
         </div>

         <div class="grid md:grid-cols-3 gap-1 w-full">
            <div class="form-item">
               <label for="lowestPrice">Preço mais baixo</label>
               <input id="lowestPrice" name="lowestPrice" type="number" min="0.01" step="0.01" placeholder="Preço" />
            </div>

            <div class="form-item">
               <label for="sizeValue">Tamanho</label>
               <div class="input">
                  <input id="sizeValue" name="sizeValue" type="number" placeholder="Tamanho" />
                  <select id="sizeUnit" name="sizeUnit" class="post-input">
                     <template x-for="[unitKey, unitLabel] in Object.entries(sizeUnits)" :key="unitKey">
                        <option x-bind:value="unitKey" x-text="unitLabel"></option>
                     </template>
                  </select>
               </div>
            </div>

            <div class="form-item">
               <label for="repeatValue">Repetição</label>
               <div class="input">
                  <input id="repeatValue" name="repeatValue" type="number" min="1" step="1" placeholder="Repetição" />
                  <select id="repeatUnit" name="repeatUnit" class="post-input">
                     <template x-for="[unitKey, unitLabel] in Object.entries(repeatUnits)" :key="unitKey">
                        <option x-bind:value="unitKey" x-text="unitLabel.plural"></option>
                     </template>
                  </select>
               </div>
            </div>
         </div>

         <div class="form-item">
            <span class="label">Tags</span>
            <ul class="flex flex-wrap items-center gap-1">
               <template x-for="[tagIndex, tag] of Object.entries(tags)" x-bind:key="tagIndex">
                  <li class="my-1">
                     <input name="tags" x-bind:id="`tag-${tagIndex}`" class="hidden" type="checkbox" x-bind:value="tagIndex" />
                     <label x-bind:for="`tag-${tagIndex}`" class="btn-outline" x-on:contextmenu.prevent="removeTag(tagIndex)">
                        <i class="checked-on ri-checkbox-circle-fill"></i>
                        <i class="checked-off ri-checkbox-blank-circle-fill"></i>
                        <span x-text="tag"></span>
                     </label>
                  </li>
               </template>
               <li>
                  <button class="btn" type="button" title="Nova tag" x-on:click="addTag">
                     <i class="ri-add-fill"></i>
                  </button>
               </li>
            </ul>
         </div>

         <div class="flex justify-center gap-2">
            <button class="btn" type="submit" x-text="editing !== false ? 'Editar' : 'Adicionar'"></button>

            <button class="btn-alt" type="button" x-show="editing !== false" x-on:click.prevent="removeProduct(editing, true)" x-cloak>
               <i class="ri-delete-bin-2-line"></i> Apagar
            </button>
         </div>
      </form>

   </div>
</div>
