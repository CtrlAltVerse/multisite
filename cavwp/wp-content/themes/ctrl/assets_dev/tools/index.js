// https://www.calculatorsoup.com/
// https://www.4devs.com.br/calculadora_porcentagem

document.addEventListener('alpine:init', () => {
   Alpine.data('tools', function () {
      return {
         favoritesIds: this.$persist([]).as('cav-tools-favorites'),
         favorites: [],
         tools: [],

         init() {
            this.$watch('favoritesIds', this.populateLists.bind(this))
            this.populateLists()
         },

         populateLists() {
            this.tools = tools.list.filter(
               (tool) => !this.favoritesIds.includes(tool.ID)
            )

            this.favorites = tools.list.filter((tool) =>
               this.favoritesIds.includes(tool.ID)
            )
         },

         toggleFavorite(id) {
            const idx = this.favoritesIds.indexOf(id)
            if (idx === -1) {
               this.favoritesIds.push(id)
            } else {
               this.favoritesIds.splice(idx, 1)
            }
         },

         toggleToolsMenu() {
            const toolsMenu = document.getElementById('tools-menu')

            setTimeout(() => {
               if (toolsMenu.open) {
                  toolsMenu.close()
               } else {
                  toolsMenu.showModal()
               }
            }, 5)
         },
      }
   })
})
