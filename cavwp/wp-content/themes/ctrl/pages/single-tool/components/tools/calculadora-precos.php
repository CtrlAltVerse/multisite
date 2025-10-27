<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolPricesRatio', function() {
         return {
            mesure: this.$persist('weight').as('tools-prices-ratio-mesure'),
            cheapest: 'none',
            currentMesureLevels: [],
            product1: {
               price: 0,
               mesure: 0,
               level: 1,
               compare: ''
            },
            product2: {
               price: 0,
               mesure: 0,
               level: 1,
               compare: ''
            },
            mesureCats: [{
                  label: 'Peso',
                  value: 'weight',
                  levels: [{
                        cat: 'SI',
                        itens: [{
                              label: 'mg',
                              ratio: 0.001
                           },
                           {
                              label: 'g',
                              ratio: 1
                           },
                           {
                              label: 'kg',
                              ratio: 1000
                           }
                        ],
                     },
                     {
                        cat: 'Imperial',
                        itens: [{
                              label: 'oz',
                              ratio: 28.349523125
                           },
                           {
                              label: 'lb',
                              ratio: 453.59237
                           }
                        ]
                     }
                  ],
               },
               {
                  label: 'Volume',
                  value: 'volume',
                  levels: [{
                        cat: 'SI',
                        itens: [{
                              label: 'ml',
                              ratio: 0.001
                           },
                           {
                              label: 'l',
                              ratio: 1
                           }
                        ]
                     },
                     {
                        cat: 'US',
                        itens: [{
                              label: 'oz',
                              ratio: 0.00295735295625
                           },
                           {
                              label: 'gal',
                              ratio: 3.785411784
                           }
                        ]
                     },
                     {
                        cat: 'Imperial',
                        itens: [{
                              label: 'oz',
                              ratio: 0.00284130625
                           },
                           {
                              label: 'gal',
                              ratio: 4.54609
                           }
                        ]
                     }
                  ],
               },
               {
                  label: 'Comprimento',
                  value: 'length',
                  levels: [{
                        cat: 'SI',
                        itens: [{
                              label: 'mm',
                              ratio: 0.0001
                           },
                           {
                              label: 'cm',
                              ratio: 0.01
                           },
                           {
                              label: 'm',
                              ratio: 1
                           },
                           {
                              label: 'km',
                              ratio: 1000
                           }
                        ]
                     },
                     {
                        cat: 'Imperial',
                        itens: [{
                              label: 'in',
                              ratio: 0.00254
                           },
                           {
                              label: 'ft',
                              ratio: 0.3048
                           },
                           {
                              label: 'mi',
                              ratio: 1609.344
                           },
                        ]
                     }
                  ],
               }
            ],

            init() {
               const changeMesureLevels = (newMesure) => {
                  const mesure = this.mesureCats.find((cat) => cat.value === newMesure)
                  this.currentMesureLevels = mesure.levels
                  this.product1.level = 1
                  this.product2.level = 1
               }

               this.$watch('mesure', changeMesureLevels)
               changeMesureLevels(this.mesure)

               this.$watch('product1', this.calcPrices.bind(this))
               this.$watch('product2', this.calcPrices.bind(this))
               this.$watch('currentMesureLevels', this.calcPrices.bind(this))
            },

            calcPrices() {
               if (this.product1.mesure <= 0 || this.product1.price <= 0 || this.product2.mesure <= 0 || this.product2.price <= 0) {
                  this.product1.compare = ''
                  this.product2.compare = ''
                  return
               }

               const qtd1 = this.product1.mesure * this.product1.level
               let level1
               this.currentMesureLevels.forEach(({
                  itens
               }) => {
                  if (level1) {
                     return
                  }
                  level1 = itens.find((lvl) => lvl.ratio === this.product1.level)
               })

               const qtd2 = this.product2.mesure * this.product2.level
               let level2
               this.currentMesureLevels.forEach(({
                  itens
               }) => {
                  if (level2) {
                     return
                  }
                  level2 = itens.find((lvl) => lvl.ratio === this.product2.level)
               })

               // product1
               let ratio_price1 = (qtd2 / qtd1) * this.product1.price
               let ratio_price1_locale = new Intl.NumberFormat("pt-BR", {
                  style: "currency",
                  currency: "BRL"
               }).format(ratio_price1)
               this.product1.compare = this.product2.mesure + level2.label + ' por ' + ratio_price1_locale

               // product2
               let ratio_price2 = (qtd1 / qtd2) * this.product2.price
               let ratio_price2_locale = new Intl.NumberFormat("pt-BR", {
                  style: "currency",
                  currency: "BRL"
               }).format(ratio_price2)
               this.product2.compare = this.product1.mesure + level1.label + ' por ' + ratio_price2_locale

               switch (true) {
                  case ratio_price1 < this.product2.price:
                     this.cheapest = 'product1'
                     break;

                  case ratio_price1 > this.product2.price:
                     this.cheapest = 'product2'
                     break;

                  default:
                     this.cheapest = '='
                     break;
               }
            }
         }
      })
   })
</script>
<form x-data="toolPricesRatio">
   <ul class="subcat">
      <template x-for="cat in mesureCats">
         <li x-bind:class="{'bg-zinc-500 text-neutral-200': mesure === cat.value}">
            <button class="btn" x-bind:class="{'active': mesure === cat.value }" type="button"
               x-on:click.prevent="mesure = cat.value" x-text="cat.label"></button>
         </li>
      </template>
   </ul>
   <div class="flex gap-4">
      <div>
         <h2 class="font-bold text-lg mb-3">
            Produto 1
            <span x-show="cheapest === 'product1'" x-cloak>
               é mais barato
            </span>
         </h2>
         <?php get_page_component('single-tool', 'input-price-ratio', ['id' => 'product1']); ?>
      </div>
      <div>
         <h2 class="font-bold text-lg mb-3">
            Produto 2
            <span x-show="cheapest === 'product2'" x-cloak>
               é mais barato
            </span>
         </h2>
         <?php get_page_component('single-tool', 'input-price-ratio', ['id' => 'product2']); ?>
      </div>
   </div>
</form>
