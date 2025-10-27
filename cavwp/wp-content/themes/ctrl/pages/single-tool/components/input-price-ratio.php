<?php $id = $args['id']; ?>
<div class="flex flex-col gap-2">
   <div class="input flex gap-2 items-center focus-within:!border-neutral-100">
      <input class="w-full" x-model.number="<?php echo $id; ?>.mesure"
             type="number" min="0" step="0.5" placeholder="Quantidade">
      <select class="input !border-0 !py-0 w-19 text-left font-semibold"
              x-model.number="<?php echo $id; ?>.level">
         <template x-for="{cat,itens} in currentMesureLevels">
            <optgroup x-bind:label="cat">
               <template x-for="{label,ratio} in itens">
                  <option x-text="label" x-bind:value="ratio"
                          x-bind:selected="<?php echo $id; ?>.level === ratio">
                  </option>
               </template>
            </optgroup>
         </template>
      </select>
   </div>
   <div class="my-1 text-center">por</div>
   <div class="input flex gap-2 items-center focus-within:!border-neutral-100">
      <span class="font-semibold">R$</span>
      <input class="w-full"
             x-model.number="<?php echo $id; ?>.price" type="number"
             min="0" step="0.01" placeholder="Valor">
   </div>
   <output class="block text-center font-semibold"
           x-text="<?php echo $id; ?>.compare"></output>
</div>
