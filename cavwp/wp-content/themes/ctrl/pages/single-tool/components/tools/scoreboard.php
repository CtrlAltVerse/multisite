<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolScoreboard', function() {
         return {
            players: this.$persist([{
               name: "Jogador 1",
               color: '#00f',
               points: 0
            }, {
               name: "Jogador 2",
               color: '#f00',
               points: 0
            }]),
            nextColor: 0,
            colors: [{
                  name: 'Azul',
                  hex: '#00f'
               },
               {
                  name: 'Vermelho',
                  hex: '#f00'
               },
               {
                  name: 'Verde',
                  hex: '#090'
               },
               {
                  name: 'Amarelo',
                  hex: '#fd2',
                  dark: 1
               },
               {
                  name: 'Preto',
                  hex: '#000',
               },
               {
                  name: 'Branco',
                  hex: '#fff',
                  dark: 1
               },
               {
                  name: 'Rosa',
                  hex: '#e49',
               },
               {
                  name: 'Roxo',
                  hex: '#92f',
               },
               {
                  name: 'Lima',
                  hex: '#cf7',
                  dark: 1
               },
               {
                  name: 'Laranja',
                  hex: '#f81',
               },
               {
                  name: 'Ciano',
                  hex: '#0ff',
                  dark: 1
               },
               {
                  name: 'Cinza',
                  hex: '#777',
               },
            ],

            addPlayer(e) {
               const form = new FormData(e.target)
               const name = form.get('name')
               const points = form.get('points')
               const color = this.colors[this.nextColor].hex

               this.players.push({
                  name,
                  color,
                  points
               })
            },

            removePlayer(index) {
               this.players.splice(index, 1)
            },

            editPlayer(index, step = 1, points = null) {
               const currentPlayer = this.players.find((_i, idx) => idx === index);

               if (!currentPlayer) {
                  return
               }

               currentPlayer.points += Number.parseInt(step)

               if ((typeof points === 'number' || typeof points === 'string') && currentPlayer.points !== points) {
                  currentPlayer.points = Number.parseInt(points)
               }
            },
         }
      })
   })
</script>
<div x-data="toolScoreboard">
   <ul class="flex flex-col gap-3 mb-8 text-lg">
      <template x-for="({name,color,points},index) in players">
         <li class="flex items-center gap-1.5">
            <button class="btn-alt" type="button" x-on:click.prevent="removePlayer(index)">
               <i class="ri-delete-bin-2-line"></i>
            </button>
            <span class="size-10 rounded-sm" x-bind:style="`background: ${color}`"></span>
            <span class="grow text-xl" x-text="name"></span>
            <button class="btn-alt" type="button" x-on:click.prevent="editPlayer(index,-5)">
               -5
            </button>
            <button class="btn-alt" type="button" x-on:click.prevent="editPlayer(index,-1)">
               -1
            </button>
            <input class="py-1.5 px-3 w-20 text-center font-bold text-2xl rounded border dark:border-neutral-100" type="number" step="1" x-bind:value="points" x-on:input.prevent="editPlayer(index,0,$el.value)" />
            <button class="btn-alt" type="button" x-on:click.prevent="editPlayer(index)">
               +1
            </button>
            <button class="btn-alt" type="button" x-on:click.prevent="editPlayer(index,10)">
               +10
            </button>
         </li>
      </template>
   </ul>
   <form class="flex gap-2" x-on:submit.prevent="addPlayer">
      <select name="color" class="py-1.5 px-3 rounded" x-model="nextColor" x-bind:style="`background: ${colors[nextColor].hex}`" x-bind:class="{'text-neutral-900': colors[nextColor].dark ?? 0}">
         <template x-for="({name,hex, dark},index) in colors">
            <option class="size-10" x-bind:value="index" x-text="name" x-bind:class="dark ?? 0 ? '!text-neutral-900' : '!text-neutral-100'" x-bind:style="`background: ${hex}`"></option>
         </template>
      </select>
      <input name="name" class="grow py-1.5 px-3 text-xl rounded border dark:border-neutral-100" type="text" maxlength="20" required />
      <input name="points" class="py-1.5 px-3 w-20 text-center font-bold text-xl rounded border dark:border-neutral-100" type="number" step="1" value="0" />
      <button class="btn" type="submit">
         <i class="ri-add-circle-line"></i>
         Adicionar
      </button>
   </form>
</div>
