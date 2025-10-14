import Alpine from 'alpinejs'
import cav from '@ctrlaltvers/alpine'

Alpine.plugin(cav)

window.Alpine = Alpine

Alpine.data('cav', function () {
   return {
      tabletop: 0,
      web: 0,
      print: 0,

      init() {
         this.$watch('tabletop', (position) => {
            this.shiftLeft('tabletop', position)
         })
         this.$watch('web', (position) => {
            this.shiftLeft('web', position)
         })
         this.$watch('print', (position) => {
            this.shiftLeft('print', position)
         })
      },

      shiftLeft(element, position) {
         const el = document.getElementById(`${element}-list`)

         el.style.left = `-${position * 100}vw`
      },
   }
})

Alpine.start()
