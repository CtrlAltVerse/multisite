<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolTimer', function() {
         return {
            current: 0,
            initial: 10,
            increment: 0,
            isFullscreen: false,
            timer: {
               p1: 0,
               p2: 0,
            },

            init() {
               this.$watch('initial', () => {
                  this.setTimer()
               })
               this.setTimer()

               const mainTimer = setInterval(() => {
                  if (this.current <= 0) {
                     return
                  }

                  if (this.timer[`p${this.current}`] === 0) {
                     this.current = this.current * -1
                     return
                  }

                  this.timer[`p${this.current}`]--
               }, 1000)
            },

            toggleTimer(next) {
               if (this.timer.p1 === 0 || this.timer.p2 === 0) {
                  return
               }

               this.timer[`p${this.current}`] = Number(this.timer[`p${this.current}`]) + Number(this.increment)

               this.current = next
            },

            setTimer() {
               const secs = this.initial * 60
               this.timer.p1 = secs
               this.timer.p2 = secs
            },

            parseSec(time) {
               const min = Math.floor(time / 60)
               const sec = time - (min * 60)
               return `${min.toString().padStart(2,'0')}:${sec.toString().padStart(2,'0')}`
            },

            toggleFullscreen() {
               if (this.isFullscreen) {
                  document.exitFullscreen()
               } else {
                  document.getElementById('Timer').requestFullscreen()
               }
               this.isFullscreen = !this.isFullscreen
            },

            reset() {
               if (this.isFullscreen) {
                  this.toggleFullscreen()
               }

               if (!confirm('Reiniciar?')) {
                  return
               }

               this.current = 0
               this.setTimer()
            },

            setClass(player) {
               return {
                  'bg-blue-800': this.current === player,
                  'bg-neutral-600': this.timer[`p${player}`] === 0
               }
            }
         }
      })
   })
</script>
<div x-data="toolTimer">
   <div id="Timer" class="relative flex portrait:flex-col w-full h-dvh font-bold">
      <div class="absolute top-4 portrait:top-1/2 left-1/2 inset-x-0 portrait:-translate-y-1/2 -translate-x-1/2 w-full text-lg">
         <div class="flex justify-center gap-4">
            <button class="btn" type="button" x-show="current>0" x-on:click.prevent="reset()">Reiniciar</button>
            <label class="input flex flex-col" x-show="current<=0">
               <span class="whitespace-nowrap text-sm">Tempo inicial</span>
               <span class="items-center gap-1 whitespace-nowrap"><i class="ri-time-line"></i> <input class="w-8 sm:w-12" type="number" min="1" max="99" x-model="initial" aria-label="Tempo inicial em minutos" />min</span>
            </label>
            <label class="input flex flex-col" x-show="current<=0">
                <span class="whitespace-nowrap text-sm">Incremento</span>
                <span class="items-center gap-1 whitespace-nowrap"><i class="ri-add-box-line"></i> <input class="w-8 sm:w-12" type="number" min="0" max="60" step="15" x-model="increment" aria-label="Incremento em segundos" />seg</span>
            </label>
            <button class="btn" x-on:click.prevent="toggleFullscreen()" aria-label="Alternar tela cheia">
               <i x-bind:class="isFullscreen ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-fill'"></i>
               <span class="hidden xs:inline">Tela cheia</span>
            </button>
         </div>
      </div>
      <button class="grow flex flex-col justify-center gap-1" type="button" x-on:click.prevent="toggleTimer(1)" x-bind:class="setClass(2)" aria-label="Passar turno ao jogador um">
         <span class="block portrait:rotate-180 text-7xl" x-text="parseSec(timer.p2)"></span>
         <span class="landscape:hidden">vs</span>
         <span class="hidden portrait:block rotate-180 text-xl" x-text="parseSec(timer.p1)"></span>
      </button>
      <button class="grow flex flex-col justify-center gap-1" type="button" x-on:click.prevent="toggleTimer(2)" x-bind:class="setClass(1)" aria-label="Passar turno ao jogador dois">
         <span class="landscape:hidden text-xl" x-text="parseSec(timer.p2)"></span>
         <span class="landscape:hidden">vs</span>
         <span class="text-7xl" x-text="parseSec(timer.p1)"></span>
      </button>
   </div>
</div>
