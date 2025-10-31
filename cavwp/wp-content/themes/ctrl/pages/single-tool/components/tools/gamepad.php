<script>
   const layouts = {
      layout1: [
         'btn-y',
         'btn-b',
         'btn-a',
         'btn-x',
         'left.bumper',
         'right.bumper',
         'left.trigger',
         'right.trigger',
         'select',
         'start',
         'left.axis',
         'right.axis',
         'btn-up',
         'btn-down',
         'btn-left',
         'btn-right',
         1,
      ],
      layout2: [
         'btn-a',
         'btn-b',
         'btn-x',
         'btn-y',
         'left.bumper',
         'right.bumper',
         'left.trigger',
         'right.trigger',
         'select',
         'start',
         'left.axis',
         'right.axis',
         'btn-up',
         'btn-down',
         'btn-left',
         'btn-right',
         2,
      ],
   }

   document.addEventListener('alpine:init', () => {
      Alpine.data('toolGamepad', function() {
         return {
            current: -1,
            layout: null,
            gamepads: [],

            init() {
               this.$watch('current', (value) => {
                  if (value === -1) {
                     return;
                  }

                  this.layout =
                     this.gamepads[this.current].id.indexOf('STANDARD GAMEPAD') > -1 ?
                     layouts.layout2 :
                     layouts.layout1
               })

               addEventListener('gamepadconnected', () => {
                  this.updateGamepadList()
               })

               addEventListener('gamepaddisconnected', () => {
                  this.updateGamepadList()
               })
            },

            vibrate() {
               this.gamepads[this.current].vibrationActuator.playEffect('dual-rumble', {
                  startDelay: 0,
                  duration: 666,
                  weakMagnitude: 1.0,
                  strongMagnitude: 1.0,
               })
            },


            makeFloat(float) {
               return parseFloat(Math.round(float * 50) + 50).toFixed(5) + '%'
            },

            updateGamepad() {
               const gamepads = navigator.getGamepads ?
                  navigator.getGamepads() :
                  navigator.webkitGetGamepads ?
                  navigator.webkitGetGamepads : []
               const gamepad = gamepads[this.current]

               if (this.layout[16] === 1) {
                  const arrows = gamepad.axes[9]

                  if (
                     (arrows >= -1 && arrows < -0.7) ||
                     (arrows >= 0.9 && arrows < 1.1)
                  ) {
                     document.querySelector(`.${layout[12]}`).classList.add('pressed')
                  } else {
                     document.querySelector(`.${layout[12]}`).classList.remove('pressed')
                  }

                  if (arrows > -0.72 && arrows < -0.13) {
                     document.querySelector(`.${layout[15]}`).classList.add('pressed')
                  } else {
                     document.querySelector(`.${layout[15]}`).classList.remove('pressed')
                  }

                  if (arrows > -0.15 && arrows < 0.43) {
                     document.querySelector(`.${layout[13]}`).classList.add('pressed')
                  } else {
                     document.querySelector(`.${layout[13]}`).classList.remove('pressed')
                  }

                  if (arrows > 0.41 && arrows <= 1) {
                     document.querySelector(`.${layout[14]}`).classList.add('pressed')
                  } else {
                     document.querySelector(`.${layout[14]}`).classList.remove('pressed')
                  }
               }

               for (let i = 0; i < 16; i++) {
                  if (gamepad.buttons[i].pressed) {
                     if (i === 6 || i === 7) {
                        const position = 72 - (72 * gamepad.buttons[i].value).toFixed(5)

                        document.querySelector(`.${this.layout[i]}`).style.backgroundPosition = `0 ${position}px`
                     } else {
                        document.querySelector(`.${this.layout[i]}`).classList.add('bg-blue-600')
                     }
                  } else {
                     if (i === 6 || i === 7) {
                        document.querySelector(`.${this.layout[i]}`).style.backgroundPosition = `0 72px`
                     } else {
                        document.querySelector(`.${this.layout[i]}`)?.classList.remove('bg-blue-600')
                     }
                  }
               }

               const leftX = gamepad.axes[0]
               const leftY = gamepad.axes[1]
               const rightX = gamepad.axes[2]
               const rightY = this.layout[16] === 1 ? gamepad.axes[5] : gamepad.axes[3]

               if (Math.abs(leftX) !== 0) {
                  document.querySelector(`.axis.left .pointer`).style.left = this.makeFloat(leftX)
               }

               if (Math.abs(leftY) !== 0) {
                  document.querySelector(`.axis.left .pointer`).style.top = this.makeFloat(leftY)
               }

               if (Math.abs(rightX) !== 0) {
                  document.querySelector(`.axis.right .pointer`).style.left = this.makeFloat(rightX)
               }

               if (Math.abs(rightY) !== 0) {
                  document.querySelector(`.axis.right .pointer`).style.top = this.makeFloat(rightY)
               }

               requestAnimationFrame(this.updateGamepad.bind(this))
            },

            updateGamepadList() {
               this.gamepads = navigator.getGamepads ?
                  navigator.getGamepads() :
                  navigator.webkitGetGamepads ?
                  navigator.webkitGetGamepads : []

               this.current = this.gamepads.findIndex((gamepad) => gamepad !== null)
               setTimeout(() => {
                  this.updateGamepad()
               }, 1)
            }
         }
      })
   })
</script>
<div x-data="toolGamepad" class="flex flex-col gap-6">
   <nav class="text-xs sm:text-base">
      <ul class="flex gap-2">
         <template x-for="(gamepad,idx) in gamepads">
            <li>
               <button type="button" x-bind:class="gamepad!==null ? 'btn' : 'btn-alt'" x-bind:disabled="gamepad===null" x-on:click.prevent="current=idx" x-bind:aria-label="`Selecionar gamepad ${idx}`">
                  Gamepad <span class="rounded px-2 bg-neutral-100 text-neutral-900" x-text="idx"></span>
               </button>
            </li>
         </template>
      </ul>
   </nav>

   <div class="flex justify-between items-center">
      <strong class="text-xs sm:text-base line-clamp-2" x-text="gamepads[current]?.id"></strong>
      <button class="btn" type="button" x-on:click.prevent="vibrate()" x-show="gamepads[current]?.vibrationActuator">
         Vibrar
      </button>
   </div>

   <div class="flex flex-col gap-2 mx-auto w-full max-w-5xl" x-show="current>=0" x-cloak>
      <div class="flex justify-around">
         <div class="flex items-start gap-3">
            <div class="input h-18 bg-no-repeat bg-position-[0_72px] bg-linear-to-t from-blue-600 to-blue-600 trigger left">
               <span x-text="layout && layout[16] === 1 ? 'LT' : 'L2'"></span>
            </div>
            <div class="input bumper left">
               <span x-text="layout && layout[16] === 1 ? 'LB' : 'L1'"></span>
            </div>
         </div>
         <div class="flex items-start gap-3">
            <div class="input bumper right">
               <span x-text="layout && layout[16] === 1 ? 'RB' : 'R1'"></span>
            </div>
            <div class="input h-18 bg-no-repeat bg-position-[0_72px] bg-linear-to-t from-blue-600 to-blue-600 trigger right">
               <span x-text="layout && layout[16] === 1 ? 'RT' : 'R2'"></span>
            </div>
         </div>
      </div>

      <div class="flex justify-between items-center gap-2">
         <div class="flex justify-center items-center gap-2 text-lg sm:text-2xl">
            <div class="input btn-left">
               <i class="ri-arrow-left-s-line"></i>
            </div>

            <div class="flex flex-col justify-between gap-8">
               <div class="input btn-up">
                  <i class="ri-arrow-up-s-line"></i>
               </div>

               <div class="input btn-down">
                  <i class="ri-arrow-down-s-line"></i>
               </div>
            </div>

            <div class="input btn-right">
               <i class="ri-arrow-right-s-line"></i>
            </div>
         </div>

         <div class="flex flex-col gap-3 text-center text-sm sm:text-base">
            <div class="input start">Start</div>
            <div class="input select">Select</div>
         </div>

         <div class="flex justify-center items-center gap-2 text-base sm:text-xl">
            <div class="input btn-x">
               <span x-show="layout && layout[16] === 1" class="font-bold">X</span>
               <span x-show="layout && layout[16] === 2"><i class="ri-square-line"></i></span>
            </div>

            <div class="flex flex-col justify-between gap-8">
               <div class="input btn-y">
                  <span x-show="layout && layout[16] === 1" class="font-bold">Y</span>
                  <span x-show="layout && layout[16] === 2"><i class="ri-triangle-line"></i></span>
               </div>
               <div class="input btn-a">
                  <span x-show="layout && layout[16] === 1" class="font-bold">A</span>
                  <span x-show="layout && layout[16] === 2"><i class="ri-close-large-fill"></i></span>
               </div>
            </div>

            <div class="input btn-b">
               <span x-show="layout && layout[16] === 1" class="font-bold">B</span>
               <span x-show="layout && layout[16] === 2"><i class="ri-circle-line"></i></span>
            </div>

         </div>
      </div>

      <div class="flex justify-around font-bold">
         <div class="relative input !rounded-full size-30 axis left">
            <span class="absolute -translate-1/2 input flex items-center justify-center !rounded-full size-8 bg-neutral-800 pointer">L</span>
         </div>
         <div class="relative input !rounded-full size-30 axis right">
            <span class="absolute -translate-1/2 input flex items-center justify-center !rounded-full size-8 bg-neutral-800 pointer">R</span>
         </div>
      </div>
   </div>

   <div x-show="gamepads.length === 0">
      <div class="flex flex-col gap-1 text-neutral-500 text-center text-sm">
         <strong><i class="ri-gamepad-line"></i> Nenhum gamepad encontrado.</strong>
         <small>Conecte um gamepad e aperte um botão para iniciar.</small>
      </div>
   </div>
</div>
