<?php $id = $args['id']; ?>
<div class="form-item">
   <div class="input">
      <input x-model.number="<?php echo $id; ?>.mesure"
         type="number" min="0" step="0.5" placeholder="Quantidade">
      <select class="post-input"
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
</div>

<div class="my-1 text-center">por</div>

<div class="form-item">
   <div class="input">
      <span class="pre-input">R$</span>
      <input
         x-model.number="<?php echo $id; ?>.price" type="number"
         min="0" step="0.01" placeholder="Valor">
   </div>
</div>
<output class="block text-center font-semibold"
   x-text="<?php echo $id; ?>.compare"></output>
