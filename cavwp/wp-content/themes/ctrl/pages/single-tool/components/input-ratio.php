<?php

$id = $args['id'];

?>
<div class="form-item">
<input type="number" placeholder="Número" min="0" step="1"
       x-model.number="pieces.<?php echo $id; ?>"
       x-bind:class="{'bg-neutral-900 dark:bg-neutral-100': highlight === '<?php echo $id; ?>'}">
       </div>
