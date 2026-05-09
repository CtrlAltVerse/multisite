<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolProductList', function() {
         return {
            list: this.$persist([{
               id: 1,
               name: 'Arroz',
               lowestPrice: 20.99,
               tags: ['2'],
               quantity: 1,
               sizeValue: 5,
               sizeUnit: 'kg',
               repeatValue: 1,
               repeatUnit: 'months',
               lastCheck: 0,
               nextCheck: 0,
            }, {
               id: 2,
               name: 'Feijão',
               lowestPrice: 10.99,
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
            showingUnitPrice: [],

            init() {
               this.updateFilter()
               this.$watch('list', () => this.updateFilter())
               this.$watch('tags', () => this.updateFilter())

               this.list = this.list.map((product, idx) => {
                  if (!product.id) {
                     product.id = idx
                  }

                  return product
               })

               this.updateList()
               this.$watch('list', () => this.updateList())
               this.$watch('tags', () => this.updateList())
               this.$watch('currentTag', () => this.updateList())

               this.$watch('editing', (id) => this.setEditing(id))
            },

            updateList() {
               let list = this.list
               if (this.currentTag) {
                  list = list.filter((product) => product.tags.includes(this.currentTag))
               }

               list.sort((a, b) => a.nextCheck - b.nextCheck)

               this.refreshList = list
            },

            setEditing(id) {
               if (id === false) {
                  return
               }

               const productIdx = this.findIndexProduct(id)

               const {
                  name,
                  lowestPrice,
                  tags,
                  quantity,
                  sizeValue,
                  sizeUnit,
                  repeatValue,
                  repeatUnit
               } = this.list[productIdx]

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

               this.$do('scroll', '#addProduct')
            },

            findIndexProduct(id) {
               return this.list.findIndex((product) => id === product.id);
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
                  id: Date.now(),
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
               document.getElementById('name').focus()
            },

            patchProduct(id, key, value) {
               const productIdx = this.findIndexProduct(id)

               this.list[productIdx][key] = value
               const {
                  lastCheck,
                  repeatValue,
                  repeatUnit
               } = this.list[productIdx]

               if (!lastCheck) {
                  this.list[productIdx]['nextCheck'] = 0
               } else {
                  if (lastCheck && repeatValue && repeatUnit) {
                     this.list[productIdx]['nextCheck'] = this.$time.plus(lastCheck, repeatValue, repeatUnit)
                  }
               }
            },

            putProduct(e) {
               const productIdx = this.findIndexProduct(this.editing)
               const formData = new FormData(e.currentTarget)

               this.list[productIdx]['lowestPrice'] = formData.get('lowestPrice')
               this.list[productIdx]['quantity'] = formData.get('quantity')
               this.list[productIdx]['sizeValue'] = formData.get('sizeValue')
               this.list[productIdx]['sizeUnit'] = formData.get('sizeUnit')
               this.list[productIdx]['repeatValue'] = formData.get('repeatValue')
               this.list[productIdx]['repeatUnit'] = formData.get('repeatUnit')

               let tags = []
               document.getElementsByName('tags').forEach(tagEl => {
                  if (tagEl.checked) {
                     tags.push(tagEl.value)
                  }
               })
               this.list[productIdx]['tags'] = tags

               this.patchProduct(this.editing, 'name', formData.get('name'))

               this.editing = false
               e.currentTarget.reset()
               document.getElementById('name').focus()
            },

            removeProduct(id, fromForm = false) {
               const productIdx = this.findIndexProduct(id)

               if (!confirm(`Remover ${this.list[productIdx].name} da lista?`)) {
                  return
               }

               this.list.splice(productIdx, 1)

               if (fromForm) {
                  document.getElementById('addProduct').reset()
               }
            },

            togglePrice(product) {
               if (product.sizeValue === 1 && ['un', 'l', 'kg'].includes(product.sizeUnit)) {
                  return
               }

               if (this.showingUnitPrice.includes(product.id)) {
                  this.showingUnitPrice.splice(this.showingUnitPrice.indexOf(product.id), 1)
               } else {
                  this.showingUnitPrice.push(product.id)
               }
            },

            priceUnit(product, place = 'price') {
               if (place === 'label') {
                  let unit = product.sizeUnit
                  switch (product.sizeUnit) {
                     case 'g':
                     case 'mg':
                        unit = 'kg'
                        break;

                     case 'ml':
                        unit = 'l'
                        break;

                     default:
                        break;
                  }
                  return `/1${unit} <i class="ri-record-circle-line"></i>`
               }

               let price
               switch (product.sizeUnit) {
                  case 'g':
                  case 'ml':
                     price = product.lowestPrice / product.sizeValue * 1000
                     break

                  case 'mg':
                     price = product.lowestPrice / product.sizeValue * 1000000
                     break;

                  default:
                     price = product.lowestPrice / product.sizeValue
                     break;
               }

               return price.toFixed(2)
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

            removeTag(tagIdx) {
               if (!confirm(`Remover tag ${this.tags[tagIdx]} de todos os produtos?`)) {
                  return
               }

               delete this.tags[tagIdx]

               this.list = this.list.map(item => {
                  item.tags.splice(item.tags.indexOf(tagIdx), 1)
                  return item
               })

               if (tagIdx === this.currentTag) {
                  this.currentTag = false
               }
            },

            updateFilter() {
               const tags = Object.entries(this.tags).map(([tagIdx, tag]) =>
                  ({
                     key: tagIdx,
                     name: tag,
                     count: this.list.filter((product) => product.tags.includes(tagIdx)).length
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

            remainingDays(id) {
               const productIdx = this.findIndexProduct(id)

               const {
                  nextCheck
               } = this.list[productIdx]

               if (nextCheck) {
                  const {
                     amount,
                     metric
                  } = this.$time.diff(nextCheck)

                  if (['seconds', 'minutes', 'hours'].includes(metric)) {
                     this.patchProduct(id, 'lastCheck', false)
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
         <template x-for="item in refreshList" x-bind:key="item.id">
            <li class="flex items-center gap-1" x-bind:class="{'opacity-75': item.lastCheck}">
               <button class="btn-alt hidden sm:inline" type="button" x-on:click.prevent="removeProduct(item.id)">
                  <i class="ri-delete-bin-2-line text-lg sm:text-2xl"></i>
               </button>
               <button class="btn-alt" type="button" x-on:click.prevent="editing = item.id">
                  <i class="ri-edit-box-line text-lg sm:text-2xl"></i>
               </button>
               <div class="flex gap-1 sm:gap-2 items-baseline grow min-w-0">
                  <span class="font-semibold text-xs sm:text-base text-neutral-500" x-text="`${item.quantity}x`"></span>
                  <span class="text-sm sm:text-xl truncate" x-text="item.name"></span>
                  </span>
               </div>
               <div class="form-item !w-fit flex-none">
                  <div class="input">
                     <span class="pre-input">R$</span>

                     <input class="text-center font-semibold sm:text-lg max-w-15 sm:max-w-25" type="number" step="0.01" x-bind:value="showingUnitPrice.includes(item.id) ? priceUnit(item) : Number(item.lowestPrice).toFixed(2)" maxlength="4" x-on:blur.prevent="patchProduct(item.id,'lowestPrice', $el.value)" x-bind:disabled="showingUnitPrice.includes(item.id)" />

                     <button class="post-input" type="button" x-show="item.sizeValue" x-html="showingUnitPrice.includes(item.id) ? priceUnit(item, 'label') : `/${item.sizeValue}${item.sizeUnit} <i class=ri-checkbox-blank-circle-line></i>`" x-on:click.prevent="togglePrice(item)" x-cloak></button>
                  </div>
               </div>

               <button class="btn-alt" type="button" x-on:click.prevent="patchProduct(item.id, 'lastCheck', 0)" x-show="item.lastCheck && !item.nextCheck" x-cloak>
                  <i class="ri-arrow-up-line text-lg sm:text-2xl"></i>
               </button>

               <button class="btn-alt max-w-16 sm:max-w-20 leading-1" type="button" x-on:click.prevent="patchProduct(item.id, 'lastCheck', Date.now())" x-show="!item.lastCheck || item.nextCheck" x-cloak>
                  <i class="ri-shopping-cart-line text-lg sm:text-2xl" x-show="!item.lastCheck" x-cloak></i>
                  <span class="text-xs" x-text="remainingDays(item.id)"></span>
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
               <input id="name" name="name" type="text" placeholder="Produto" autofocus required autocapitalize="words" />
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
                     <template x-for="[unitKey, unitLabel] in Object.entries(sizeUnits)" x-bind:key="unitKey">
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
               <template x-for="[tagIdx, tag] of Object.entries(tags)" x-bind:key="tagIdx">
                  <li class="my-1">
                     <input name="tags" x-bind:id="`tag-${tagIdx}`" class="hidden" type="checkbox" x-bind:value="tagIdx" />
                     <label x-bind:for="`tag-${tagIdx}`" class="btn-outline" x-on:contextmenu.prevent="removeTag(tagIdx)">
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
            <button class="btn" type="submit">
               <i class="ri-checkbox-circle-line"></i>
               <span x-text="editing !== false ? 'Atualizar' : 'Adicionar'"></span>
            </button>

            <button class="btn-alt" type="button" x-show="editing !== false" x-on:click.prevent="removeProduct(editing, true)" x-cloak>
               <i class="ri-delete-bin-2-line"></i> Apagar
            </button>
         </div>
      </form>

   </div>
</div>
