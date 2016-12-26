<form id="rule-fm" method="post">
	<ul class="form-list">
		<li class="fields">
			<div class="field">
				<label><strong>价格规则</strong></label>
			</div>
			<div class="field">
				<label>价格:</label>
				<div class="input-box">
					<select name="priceRule_price_prefix" style="width: 76px;">
						<option value="+"<?php echo (isset($ruleData['priceRule_price_prefix']) && $ruleData['priceRule_price_prefix'] == '+') ? ' selected="selected"' : ''; ?>>+</option>
						<option value="-"<?php echo (isset($ruleData['priceRule_price_prefix']) && $ruleData['priceRule_price_prefix'] == '-') ? ' selected="selected"' : ''; ?>>-</option>
						<option value="*"<?php echo (isset($ruleData['priceRule_price_prefix']) && $ruleData['priceRule_price_prefix'] == '*') ? ' selected="selected"' : ''; ?>>*</option>
					</select>
					/
					<input type="text" class="input-text easyui-numberbox" name="priceRule_price" style="width: 76px;" data-options="precision:2,value:0,min:0" value="<?php echo isset($ruleData['priceRule_price']) ? $ruleData['priceRule_price'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields">
			<div class="field">
				<label>价格选择:<em>*</em></label>
				<div class="input-box">
					<select name="priceRule_price_id">
						<?php if (!empty($priceList)) { ?>
							<option value="">请选择</option>
							<?php foreach ($priceList as $val) { ?>
								<option value="<?php echo $val['dictionary_id']; ?>"<?php echo (isset($ruleData['priceRule_price_id']) && $ruleData['priceRule_price_id'] == $val['dictionary_id']) ? ' selected="selected"' : ''; ?>><?php echo $val['name']; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="field">
				<label>特价:</label>
				<div class="input-box">
					<select name="priceRule_special_price_prefix" style="width: 76px;">
						<option value="+"<?php echo (isset($ruleData['priceRule_special_price_prefix']) && $ruleData['priceRule_special_price_prefix'] == '+') ? ' selected="selected"' : ''; ?>>+</option>
						<option value="-"<?php echo (isset($ruleData['priceRule_special_price_prefix']) && $ruleData['priceRule_special_price_prefix'] == '-') ? ' selected="selected"' : ''; ?>>-</option>
						<option value="*"<?php echo (isset($ruleData['priceRule_special_price_prefix']) && $ruleData['priceRule_special_price_prefix'] == '*') ? ' selected="selected"' : ''; ?>>*</option>
					</select>
					/
					<input type="text" class="input-text easyui-numberbox" name="priceRule_special_price" style="width: 76px;" data-options="precision:2,value:0,min:0" value="<?php echo isset($ruleData['priceRule_special_price']) ? $ruleData['priceRule_special_price'] : ''; ?>" />
				</div>
			</div>
		</li>
	</ul>
	<ul class="form-list">
		<li class="fields">
			<div class="field">
				<label><strong>选项规则</strong></label>
			</div>
		</li>
		<?php $i = 0; ?>
		<?php $total = count($optionList); ?>
		<?php foreach ($optionList as $val) { ?>
			<?php if (($i++ % 2) == 0) { ?>
				<li class="fields">
			<?php } ?>

			<div class="field">
				<label for="option_<?php echo $val['option_id']; ?>"><?php echo $val['name']; ?></label>
				<div class="input-box">
					<input type="text" class="input-text" name="optionRule[<?php echo $val['option_id']; ?>]" id="option_<?php echo $val['option_id']; ?>" value="<?php echo isset($ruleData['optionRule[' . $val['option_id'] . ']']) ? $ruleData['optionRule[' . $val['option_id'] . ']'] : ''; ?>" />
				</div>
			</div>

			<?php if (($i % 2) == 0 || $i == $total) { ?>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
	<ul class="form-list">
		<li class="fields wide">
			<div class="field">
				<label><strong>分类规则</strong></label>
				<div class="input-box">
					<label><font color="red">[分类名称]</font></label>
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_meta_name">分类名称:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_name" id="rule-category_meta_name" value="<?php echo isset($ruleData['categoryRule_name']) ? $ruleData['categoryRule_name'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_description">描述:</label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_description" id="rule-category_description" value="<?php echo isset($ruleData['categoryRule_description']) ? $ruleData['categoryRule_description'] : ''; ?>" placeholder="可以使用[分类描述]" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_meta_title">meta标题:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_meta_title" id="rule-category_meta_title" value="<?php echo isset($ruleData['categoryRule_meta_title']) ? $ruleData['categoryRule_meta_title'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_meta_keyword">meta关键字:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_meta_keyword" id="rule-category_meta_keyword" value="<?php echo isset($ruleData['categoryRule_meta_keyword']) ? $ruleData['categoryRule_meta_keyword'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_meta_description">meta描述:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_meta_description" id="rule-category_meta_description" value="<?php echo isset($ruleData['categoryRule_meta_description']) ? $ruleData['categoryRule_meta_description'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-category_url">url:</label>
				<div class="input-box">
					<input type="text" class="input-text" name="categoryRule_url" id="rule-category_url" value="<?php echo isset($ruleData['categoryRule_url']) ? $ruleData['categoryRule_url'] : ''; ?>" />
				</div>
			</div>
		</li>
	</ul>
	<ul class="form-list">
		<li class="fields wide">
			<div class="field">
				<label><strong>产品规则</strong></label>
				<div class="input-box">
					<label><font color="red">[分类名称] [产品名称] [原价] [特价]<?php echo isset($ruleData['product_attribute']) ? '<br />' . $ruleData['product_attribute'] : ''; ?></font></label>
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_name">产品名称:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_name" id="rule-product_name" value="<?php echo isset($ruleData['productRule_name']) ? $ruleData['productRule_name'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_short_description">短描述:</label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_short_description" id="rule-product_short_description" value="<?php echo isset($ruleData['productRule_short_description']) ? $ruleData['productRule_short_description'] : ''; ?>" placeholder="可以使用[产品短描述]" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_description">描述:</label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_description" id="rule-product_description" value="<?php echo isset($ruleData['productRule_description']) ? $ruleData['productRule_description'] : ''; ?>" placeholder="可以使用[产品描述]" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_meta_title">meta标题:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_meta_title" id="rule-product_meta_title" value="<?php echo isset($ruleData['productRule_meta_title']) ? $ruleData['productRule_meta_title'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_meta_keyword">meta关键字:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_meta_keyword" id="rule-product_meta_keyword" value="<?php echo isset($ruleData['productRule_meta_keyword']) ? $ruleData['productRule_meta_keyword'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_meta_description">meta描述:<em>*</em></label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_meta_description" id="rule-product_meta_description" value="<?php echo isset($ruleData['productRule_meta_description']) ? $ruleData['productRule_meta_description'] : ''; ?>" />
				</div>
			</div>
		</li>
		<li class="fields wide">
			<div class="field">
				<label for="rule-product_url">url:</label>
				<div class="input-box">
					<input type="text" class="input-text" name="productRule_url" id="rule-product_url" value="<?php echo isset($ruleData['productRule_url']) ? $ruleData['productRule_url'] : ''; ?>" />
				</div>
			</div>
		</li>
	</ul>
</form>