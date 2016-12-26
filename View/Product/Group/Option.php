<form id="option-fm" method="post">
	<table id="dTable" class="data-table">
		<thead>
		<tr>
			<th style="width: 130px">选项名称</th>
			<th style="width: 130px">选项值</th>
			<th>价格</th>
		</tr>
		</thead>
		<?php if (!empty($optionList)) { ?>
		<?php foreach ($optionList as $option) { ?>
		<tbody>
		<tr>
			<td>
				<input type="checkbox" name="options[]" value="<?php echo $option['option_id']; ?>" id="<?php echo $option['option_id']; ?>" class="checkbox" onclick="choseOp(this)"<?php echo (isset($data['options[]']) && in_array($option['option_id'], $data['options[]'])) ? ' checked="checked"' : ''; ?> />
				<label><?php echo $option['name']; ?></label> (<?php echo $option['type'] == '1' ? '选择型' : '填入型'; ?>)
			</td>
			<td>
				<input type="checkbox" class="checkbox" name="option_requireds[]" value="<?php echo $option['option_id']; ?>"<?php echo (isset($data['option_requireds[]']) && in_array($option['option_id'], $data['option_requireds[]'])) ? ' checked="checked"' : ''; ?> />必需
			</td>
			<?php if ($option['type'] == '0') { ?>
			<td>
				<select style="width: 35px" name="option_value[<?php echo $option['option_id']; ?>][0][price_prefix]">
					<option value="+"<?php echo (isset($data['option_value[' . $option['option_id'] . '][0][price_prefix]']) && $data['option_value[' . $option['option_id'] . '][0][price_prefix]'] == '+') ? ' selected="selected"' : ''; ?>>+</option>
					<option value="-"<?php echo (isset($data['option_value[' . $option['option_id'] . '][0][price_prefix]']) && $data['option_value[' . $option['option_id'] . '][0][price_prefix]'] == '-') ? ' selected="selected"' : ''; ?>>-</option>
				</select>
				<input type="text" class="input-text easyui-numberbox" name="option_value[<?php echo $option['option_id']; ?>][0][price]" value="<?php echo isset($data['option_value[' . $option['option_id'] . '][0][price]']) ? $data['option_value[' . $option['option_id'] . '][0][price]'] : '0.00'; ?>" data-options="width:171,precision:2,value:0.00,min:0.00" />
			</td>
			<?php } ?>
		</tr>
		</tbody>
		<?php if ($option['type'] == '1') { ?>
		<tbody id="option-<?php echo $option['option_id'] ?>">
		<?php foreach ($optionValueList as $optionValue) { ?>
		<?php if ($optionValue['option_id'] == $option['option_id']) { ?>
		<tr id="tr-option-<?php echo $option['option_id']; ?>-<?php echo $optionValue['option_value_id']; ?>"  >
			<td></td>
			<td>
				<input type="checkbox" class="checkbox" name="option_values[<?php echo $option['option_id']; ?>][]" value="<?php echo $optionValue['option_value_id']; ?>" id="option-<?php echo $option['option_id']; ?>-<?php echo $optionValue['option_value_id']; ?>"<?php echo (isset($data['option_values[' . $option['option_id'] . '][]']) && in_array($optionValue['option_value_id'], $data['option_values[' . $option['option_id'] . '][]'])) ? ' checked="checked"' : ''; ?> />
				<?php echo $optionValue['name']; ?>
			</td>
			<td>
				<select style="width:35px;" name="option_value[<?php echo $option['option_id']; ?>][<?php echo $optionValue['option_value_id']; ?>][price_prefix]">
					<option value="+"<?php echo (isset($data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price_prefix]']) && $data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price_prefix]'] == '+') ? ' selected="selected"' : ''; ?>>+</option>
					<option value="-"<?php echo (isset($data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price_prefix]']) && $data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price_prefix]'] == '-') ? ' selected="selected"' : ''; ?>>-</option>
				</select>
				<input type="text" class="input-text easyui-numberbox" name="option_value[<?php echo $option['option_id']; ?>][<?php echo $optionValue['option_value_id']; ?>][price]" data-options="width:171,precision:2,value:0.00,min:0.00" value="<?php echo isset($data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price]']) ? $data['option_value[' . $option['option_id'] . '][' . $optionValue['option_value_id'] . '][price]'] : '0.00'; ?>" />
			</td>
		</tr>
		<?php }?>
		<?php }?>
		</tbody>
		<?php } ?>
		<?php } ?>
		<?php } ?>
	</table>
</form>
<script type="text/javascript">
function choseOp(checkbox) {
	if (!checkbox.checked) {
		$('input[id^=option-' + checkbox.id.toString() + '-]').prop("checked", false);
	}
}
</script>