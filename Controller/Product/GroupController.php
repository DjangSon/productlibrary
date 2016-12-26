<?php
/**
 * Class Product_GroupController
 * ByAdded       王天贵
 * DateAdded     2016-08-12
 * ByModified    雷泳涛
 * DateModified  2016-10-26
 */
class Product_GroupController extends CustomController
{
	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/group/index');
	}

	public function indexAction()
	{
		$option = array(
			'where'  => array(
				'status' => array('eq', 1)
			),
			'order'  => array('sort' => 'ASC'),
			'col'    => 'dictionary_id, name'
		);

		//获取字典中所有产品属性列表
		$option['where']['type'] = array('eq', '产品属性');
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$attributeList   = $dictionaryModel->getAllList($option);

		//获取字典中所有产品价格列表
		$option['where']['type'] = array('eq', '产品价格');
		$priceList = $dictionaryModel->getAllList($option);

		//获取所有产品选项列表
		$option = array(
			'col' => 'option_id, type, name'
		);
		$optionModel = $this->_loadModel('product/option');
		$optionList  = $optionModel->getAllList($option);

		$this->_view->assign('optionList', $optionList);
		$this->_view->assign('priceList', $priceList);
		$this->_view->assign('attributeList', $attributeList);

		$this->_view->render('product/group/index');
	}

	/**
	 * Describe      获取分组group列表（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function groupListAction()
	{
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows = isset($_POST['rows']) ? $_POST['rows'] : 25;

		$option = array(
			'col' => 'group_id, name, remarks, attributes, prices, options, by_added, by_modified, date_added, date_modified'
		);
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'name':
						if (strlen(trim($key)) > 0) {
							$option['where'][$key] = array('eq', $val);
						}
					break;
				}
			}
		}
		$groupModel = $this->_loadModel('product/group');
		$total      = $groupModel->getTotalList($option);
		$data       = array();

		if ($total) {
			$data = $groupModel->getList($page, $rows, $option);

			// 获取字典中所有的产品属性列表
			$option = array(
				'where' => array(
					'type'   => array('eq', '产品属性'),
					'status' => array('eq', 1)
				),
				'col' => 'dictionary_id, name'
			);
			$dictionaryModel = $this->_loadModel('system/dictionary');
			$attributeList   = $dictionaryModel->getPairs($option);

			// 获取字典中所有的产品价格列表
			$option['where']['type'] = array('eq', '产品价格');
			$priceList = $dictionaryModel->getPairs($option);

			// 获取产品的所有的选项列表
			$option = array(
				'col' => 'option_id, name'
			);
			$optionModel = $this->_loadModel('product/option');
			$optionList  = $optionModel->getPairs($option);

			if (!empty($data)) {
				foreach ($data as $key => $value) {
					$attributeArr = explode(',', $value['attributes']);
					$priceArr     = explode(',', $value['prices']);
					$optionArr    = explode(',', $value['options']);

					// 初始化产品的属性、价格和选项值列表
					$data[$key]['attributes'] = '';
					$data[$key]['prices']     = '';
					$data[$key]['options']    = '';

					foreach ($attributeArr as $val) {
						$data[$key]['attributes'] .= (isset($attributeList[$val]) ? $attributeList[$val] : '未知') . ',';
					}
					foreach ($priceArr as $val) {
						$data[$key]['prices'] .= (isset($priceList[$val]) ? $priceList[$val] : '未知') . ',';
					}
					foreach ($optionArr as $val) {
						$data[$key]['options'] .= (isset($optionList[$val]) ? $optionList[$val] : '未知') . ',';
					}

					$data[$key]['attributes'] = empty($data[$key]['attributes']) ? '' : rtrim($data[$key]['attributes'], ',');
					$data[$key]['prices']     = empty($data[$key]['prices']) ? '' : rtrim($data[$key]['prices'], ',');
					$data[$key]['options']    = empty($data[$key]['options']) ? '' : rtrim($data[$key]['options'], ',');
				}
			}
		}
		$return = array(
			'total' => $total,
			'rows'  => $data
		);
		$this->_ajaxReturn($return);
	}

	/**
	 * Describe      添加分组（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function addGroupAction()
	{
		$result             = array('error' => false, 'msg' => array());
		$data['name']       = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['remarks']    = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
		$data['by_added']   = $_SESSION['user_account'];
		$data['date_added'] = now();

		$groupModel = $this->_loadModel('product/group');
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '产品分组名称不能为空！';
		} elseif ($groupModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '产品分组名称已存在！';
		}

		if (!$result['error']) {
			if ($groupModel->add($data)) {
				$result['msg'][] = '产品分组名称添加成功！';
			} else {
				$result['error'] = true;
				$result['msg'][] = '产品分组名称添加失败！';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取分组（修改分组展示使用）-（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getGroupAction()
	{
		$groupId = isset($_GET['group_id']) ? $_GET['group_id'] : 0;

		// 获取当前分组group_id下的信息
		$groupModel = $this->_loadModel('product/group');
		$data       = $groupModel->get($groupId, 'name, remarks');

		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      修改分组（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateGroupAction()
	{
		$result                = array('error' => false, 'msg' => array());
		$groupId               = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$data['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['remarks']       = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();

		// 验证数据
		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '产品分组名称不能为空!';
		} elseif ($groupModel->existName($data['name'], $groupId)) {
			$result['error'] = true;
			$result['msg'][] = '产品分组名称已存在!';
		}

		if (!$result['error']) {
			if ($groupModel->update($data, $groupId)) {
				$result['msg'][] = '产品分组修改成功！';
			} else {
				$result['error'] = true;
				$result['msg'][] = '产品分组修改失败！';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      删除分组（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function delGroupAction()
	{
		$result  = array('error' => false, 'msg' => array());
		$groupId = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;

		// 验证数据
		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		$categoryGroupModel = $this->_loadModel('category/group');
		if ($categoryGroupModel->existProduct_Group_Id($groupId)) {
			$this->_returnErrorMsg('当前产品分组正在使用，无法删除！');
		}

		// 删除产品的关系表数据
		$where = array(
			'group_id' => array('eq', $groupId)
		);
		// 删除当前产品分组下的产品属性attribute
		$productToAttributeModel = $this->_loadModel('product/to/attribute');
		$productToAttributeModel->delByWhere($where);

		// 删除当前产品分组下的产品价格price
		$productToPriceModel = $this->_loadModel('product/to/price');
		$productToPriceModel->delByWhere($where);

		// 删除当前产品分组下的产品选项option
		$productToOptionModel = $this->_loadModel('product/to/option');
		$productToOptionModel->delByWhere($where);

		// 删除当前产品分组下的产品选项值option_value
		$productToOptionValueModel = $this->_loadModel('product/to/option/value');
		$productToOptionValueModel->delByWhere($where);

		// 删除当前产品分组下的产品详情
		$productToPriceModel = $this->_loadModel('product');
		$productToPriceModel->delByWhere($where);

		// 删除当前的产品分组product_group
		$productGroupModel = $this->_loadModel('product/group');
		if ($productGroupModel->del($groupId)) {
			$result['msg'][] = '产品分组，删除成功！';
		} else {
			$result['error'] = true;
			$result['msg'][] = '产品分组，删除失败！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取属性分配（产品group页面和product页面共用）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getAttributesAction()
	{
		$groupId   = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

		// 初始化返回数据
		$data = array();
		if (empty($groupId)) $this->_ajaxReturn($data);

		// 如果product_id等于0，则获取产品分组的属性列表(group页面)
		if (empty($productId)) {
			$groupModel = $this->_loadModel('product/group');
			$groupAttributeList = $groupModel->get($groupId, 'attributes');
			$data['attributes[]'] = explode(',', $groupAttributeList['attributes']);
		} else {
			// 如果product_id大于0，则获取产品的属性列表(product页面)
			$option = array(
				'where' => array(
					'product_id' => array('eq', $productId),
					'group_id'   => array('eq', $groupId)
				),
				'col' => 'attribute_id, content'
			);
			$productToAttributeModel = $this->_loadModel('product/to/attribute');
			$attrbuteValueList       = $productToAttributeModel->getAllList($option);
			if (!empty($attrbuteValueList)) {
				foreach ($attrbuteValueList as $val) {
					$data[$val['attribute_id']] = $val['content'];
				}
			}
		}
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      获取价格分配（产品group页面和product页面共用）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getPricesAction()
	{
		$groupId   = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

		// 初始化返回数据
		$data = array();
		if (empty($groupId)) $this->_ajaxReturn($data);

		// 如果product_id等于0，并且group_id大于0，则获取产品分组的价格列表(group页面)
		if (empty($productId)) {
			$groupModel       = $this->_loadModel('product/group');
			$groupPricesList  = $groupModel->get($groupId, 'prices');
			$data['prices[]'] = explode(',', $groupPricesList['prices']);
		} else {
			// 如果product_id大于0，则获取产品的价格列表(product页面)
			$option = array(
				'where' => array(
					'product_id' => array('eq', $productId),
					'group_id'   => array('eq', $groupId)
				),
				'col' => 'price_id, price, special_price'
			);
			$productToPriceModel = $this->_loadModel('product/to/price');
			$priceValueList      = $productToPriceModel->getAllList($option);
			if (!empty($priceValueList)) {
				foreach ($priceValueList as $val) {
					$data[$val['price_id'] . '-price']         = $val['price'];
					$data[$val['price_id'] . '-special_price'] = $val['special_price'];
				}
			}
		}
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      获取选项分配(产品group页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getOptionsAction()
	{
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		// 初始化返回数据
		$data = array();

		// 如果group_id大于0，则获取产品分组的选项列表
		if ($groupId) {
			$groupModel        = $this->_loadModel('product/group');
			$groupOptionsList  = $groupModel->get($groupId, 'options');
			$data['options[]'] = explode(',', $groupOptionsList['options']);
		}
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      获取产品的选项值(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getOptionValuesAction()
	{
		$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : '0' ;
		$groupId   = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		// 验证数据
		$option = array(
			'where' => array(
				'product_id' => array('eq', $productId),
				'group_id'   => array('eq', $groupId)
			)
		);
		$productModel = $this->_loadModel('product');
		if ($groupId < 1 || !$productModel->getTotalList($option)) {
			die('非法访问！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId, 'options');
		if (empty($group)) {
			die('非法访问！');
		}

		// 获取当前分组下已经分配的选项(值)option(value)列表
		// 初始化产品的选项数据
		$optionList      = array();
		$optionValueList = array();
		if (!empty($group['options'])) {
			// 获取该分组的产品选项
			$option = array(
				'where' => array(
					'option_id' => array('in', $group['options'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'option_id, type, name'
			);
			// 获取当前产品分组group_id下的产品选项值option_value
			$optionModel = $this->_loadModel('product/option');
			$optionList  = $optionModel->getAllList($option);

			// 获取当前产品分组group_id下的产品选项值option_value
			$option['col']    = 'option_value_id, option_id, name';
			$optionValueModel = $this->_loadModel('product/option/value');
			$optionValueList  = $optionValueModel->getAllList($option);
		}

		// 初始化产品的选项关系数据data
		$data = array(
			'options[]'          => array(),
			'option_requireds[]' => array()
		);
		// 如果产品product_id大于0，获取产品的选项关系数据
		if ($productId) {
			// 获取当前产品product_id下的选项option列表
			$option = array(
				'where' => array(
					'product_id' => array('eq', $productId)
				),
				'col' => 'option_id, required'
			);
			$productToOptionModel = $this->_loadModel('product/to/option');
			$productToOptionList  = $productToOptionModel->getAllList($option);

			// 遍历产品的选项option_list列表，获取当前产品的options和requires参数
			if (!empty($productToOptionList)) {
				foreach ($productToOptionList as $val) {
					$data['options[]'][$val['option_id']] = $val['option_id'];

					if ($val['required'] == '1') {
						$data['option_requireds[]'][$val['option_id']] = $val['option_id'];
					}
				}
			}

			// 获取当前产品product_id下的选项值option_value列表
			$option = array(
				'where' => array(
					'product_id' => array('eq', $productId)
				),
				'col' => 'option_id, option_value_id, price_prefix, price'
			);
			$productToOptionValueModel = $this->_loadModel('product/to/option/value');
			$productToOptionValueList  = $productToOptionValueModel->getAllList($option);

			// 遍历产品的选项option_value_list列表，获取当前产品的option_value_id、price_prefix和price参数
			if (!empty($productToOptionValueList)) {
				foreach ($productToOptionValueList as $val) {
					if ($val['option_value_id']) {
						$data["option_values[{$val['option_id']}][]"][$val['option_value_id']] = $val['option_value_id'];
					}
					$data["option_value[{$val['option_id']}][{$val['option_value_id']}][price_prefix]"] = $val['price_prefix'];
					$data["option_value[{$val['option_id']}][{$val['option_value_id']}][price]"]        = $val['price'];
				}
			}
		}
		$this->_view->assign('optionList', $optionList);
		$this->_view->assign('optionValueList', $optionValueList);
		$this->_view->assign('data', $data);

		$this->_view->render('product/group/option');
	}

	/**
	 * Describe      修改分组的属性分配(产品group页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateAttributesAction()
	{
		$result                = array('error' => false, 'msg' => array());
		$groupId               = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$attributeList         = isset($_POST['attributes']) ? $_POST['attributes'] : array();
		$data['attributes']    = empty($attributeList) ? '' : implode(',', $attributeList);
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();

		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 删除产品属性表product_to_attribute中不存在的属性(产品分组属性更新后，产品的属性也随之更新)
		$where = array(
			'group_id' => array('eq', $groupId)
		);
		if (!empty($attributeList)) $where['attribute_id'] = array('notin', $attributeList);
		$productToAttributeModel = $this->_loadModel('product/to/attribute');
		$productToAttributeModel->delByWhere($where);

		// 获取当前group_id下的原始属性attribute_id集合
		$originalGroupAttributes     = $groupModel->get($groupId, 'attributes');
		$originalGroupAttributesList = explode(',', $originalGroupAttributes['attributes']);

		// 获取当前group_id下的新添加的attribute_id集合
		$newGroupAttrList = array_diff($attributeList, $originalGroupAttributesList);

		if (!empty($newGroupAttrList)) {
			// 获取当前产品product表中，当前分组group_id下的所有的产品的product_ids集合
			$productModel = $this->_loadModel('product');
			$option = array(
				'where' => array(
					'group_id' => array('eq', $groupId)
				)
			);
			$productIds = $productModel->getCol($option);

			// 遍历当前分组下的所有的产品product_ids，为每个产品添加新的分组属性attribute_id
			if (!empty($productIds)) {
				// 获取字典中所有的产品属性集合
				$attributeModel = $this->_loadModel('system/dictionary');
				$option = array(
					'where' => array(
						'type'   => array('eq', '产品属性'),
						'status' => array('eq', 1)
					),
					'col' => 'dictionary_id, name'
				);
				$dictionaryAttributeList = $attributeModel->getPairs($option);

				// 遍历当前产品分组group_id下的所有的product_ids和新添加的属性attribute_ids
				foreach ($newGroupAttrList as $value) {
					foreach ($productIds as $val) {
						$productToAttributeData = array(
							'product_id'   => $val,
							'attribute_id' => $value,
							'group_id'     => $groupId,
							'content'      => ''
						);
						if (!$productToAttributeModel->add($productToAttributeData)) {
							$result['error'] = true;
							$result['msg'][] = '产品(' . $val . ') - 属性(' . $dictionaryAttributeList[$value] . ')，更新失败！';
						}
					}
				}
			}
		}

		// 修改产品方案规则：每次产品分组属性重新分配后，产品方案都要做清空处理！
		$option = array(
			'where' => array(
				'product_group_id' => array('eq', $groupId)
			)
		);
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroupIds   = $categoryGroupModel->getCol($option);

		if (!empty($categoryGroupIds)) {
			$option = array(
				'where' => array(
					'category_group_id' => array('in', $categoryGroupIds)
				)
			);
			$schemeModel = $this->_loadModel('scheme');
			$schemeIds   = $schemeModel->getCol($option);

			if (!empty($schemeIds)) {
				$where = array(
					'scheme_id' => array('in', $schemeIds)
				);
				$schemeData = array(
					'rule_json'     => '',
					'sub_rule_json' => '',
				);
				$schemeModel->updateByWhere($schemeData, $where);
			}
		}

		// 修改product_group数据表中的属性attribute数据
		if ($groupModel->update($data, $groupId)) {
			$result['msg'][] = '产品属性，分配成功！';
		} else {
			$result['error'] = true;
			$result['msg'][] = '产品属性，分配失败！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改分组的价格分配（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updatePricesAction()
	{
		$result                = array('error' => false, 'msg' => array());
		$groupId               = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$priceList             = isset($_POST['prices']) ? $_POST['prices'] : array();
		$data['prices']        = empty($priceList) ? '' : implode(',', $priceList);
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();

		// 验证数据
		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 删除产品属性表product_to_price中不存在的价格(产品分组价格更新后，产品的价格也随之更新)
		$where = array(
			'group_id' => array('eq', $groupId)
		);
		if (!empty($priceList)) $where['price_id'] = array('notin', $priceList);
		$productToPriceModel = $this->_loadModel('product/to/price');
		$productToPriceModel->delByWhere($where);

		// 获取当前group_id下的原始价格price集合
		$originalGroupPrice     = $groupModel->get($groupId, 'prices');
		$originalGroupPriceList = explode(',', $originalGroupPrice['prices']);

		// 获取当前group_id下的新添加的attribute_id集合
		$newGroupPriceList = array_diff($priceList, $originalGroupPriceList);

		if (!empty($newGroupPriceList)) {
			// 获取当前产品分组group_id下的所有的产品的product_ids集合
			$option = array(
				'where' => array('group_id' => array('eq', $groupId))
			);
			$productModel = $this->_loadModel('product');
			$productIds   = $productModel->getCol($option);

			// 遍历当前分组下的所有的产品product_ids，为每个产品添加新的分组价格price_id
			if (!empty($productIds)) {
				// 获取字典中所有可用的产品属性集合
				$attributeModel = $this->_loadModel('system/dictionary');
				$option = array(
					'where' => array(
						'type'   => array('eq', '产品价格'),
						'status' => array('eq', 1)
					),
					'col' => 'dictionary_id, name'
				);
				$dictionaryPriceList = $attributeModel->getPairs($option);

				// 遍历当前分组下的所有的产品product_ids，为每个产品添加新的分组价格price_ids
				foreach ($newGroupPriceList as $value) {
					foreach ($productIds as $val) {
						$productToPriceData = array(
							'product_id'    => $val,
							'price_id'      => $value,
							'group_id'      => $groupId,
							'price'         => '0.00',
							'special_price' => '0.00'
						);
						if (!$productToPriceModel->add($productToPriceData)) {
							$result['error'] = true;
							$result['msg'][] = '产品(' . $val . ') - 价格(' . $dictionaryPriceList[$value] . ')，更新失败！';
						}
					}
				}
			}
		}

		// 修改产品方案规则：每次产品分组价格重新分配后，产品方案都要做清空处理！
		$option = array(
			'where' => array(
				'product_group_id' => array('eq', $groupId)
			)
		);
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroupIds   = $categoryGroupModel->getCol($option);

		if (!empty($categoryGroupIds)) {
			$option = array(
				'where' => array(
					'category_group_id' => array('in', $categoryGroupIds)
				)
			);
			$schemeModel = $this->_loadModel('scheme');
			$schemeIds   = $schemeModel->getCol($option);

			if (!empty($schemeIds)) {
				$where = array(
					'scheme_id' => array('in', $schemeIds)
				);
				$schemeData = array(
					'rule_json'     => '',
					'sub_rule_json' => '',
				);
				$schemeModel->updateByWhere($schemeData, $where);
			}
		}

		// 修改product_group数据表中的价格price数据
		if ($groupModel->update($data, $groupId)) {
			$result['msg'][] = '产品分组价格分配成功！';
		} else {
			$result['error'] = true;
			$result['msg'][] = '产品分组价格分配失败！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改分组的选项分配（产品group页面）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateOptionsAction()
	{
		$result                = array('error' => false, 'msg' => array());
		$groupId               = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$data['options']       = isset($_POST['options']) ? implode(',', $_POST['options']) : '';
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();

		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 删除产品选项(值)表product_to_option(value)中不存在的选项(值)(产品分组选项更新后，产品的选项也随之更新)
		$where = array(
			'group_id'  => array('eq', $groupId)
		);
		if (!empty($data['options'])) $where['option_id'] = array('notin', $data['options']);
		// 删除产品-选项关系数据
		$productToOptionModel = $this->_loadModel('product/to/option');
		$productToOptionModel->delByWhere($where);

		// 删除产品-选项值关系数据
		$productToOptionValueModel = $this->_loadModel('product/to/option/value');
		$productToOptionValueModel->delByWhere($where);

		// 说明：产品分组选项和产品分组属性、价格不同，产品选项需要在产品product页面进行分配才会具有该选项

		// 修改产品方案规则：每次产品分组选项被重新分配后，产品方案都要做清空处理！
		$option = array(
			'where' => array(
				'product_group_id' => array('eq', $groupId)
			)
		);
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroupIds   = $categoryGroupModel->getCol($option);

		if (!empty($categoryGroupIds)) {
			$option = array(
				'where' => array(
					'category_group_id' => array('in', $categoryGroupIds)
				)
			);
			$schemeModel = $this->_loadModel('scheme');
			$schemeIds   = $schemeModel->getCol($option);
			if (!empty($schemeIds)) {
				$where = array(
					'scheme_id' => array('in', $schemeIds)
				);
				$schemeData = array(
					'rule_json'     => '',
					'sub_rule_json' => '',
				);
				$schemeModel->updateByWhere($schemeData, $where);
			}
		}

		// 修改product_group数据表中的选项option数据
		if ($groupModel->update($data, $groupId)) {
			$result['msg'][] = '产品分组选项分配成功！';
		} else {
			$result['error'] = true;
			$result['msg'][] = '产品分组选项分配失败!';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      批量修改状态（下架、启用、缺货）-（产品group页面）
	 * ByAdded       黄力军
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function batchStatusAction()
	{
		$result  = array('error' => false, 'msg' => array());
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$status  = (isset($_GET['status']) && in_array($_GET['status'], array(0, 1, 2))) ? $_GET['status'] : 0;

		// 验证数据
		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		if (!isset($_FILES['batch-fl']['error']) || $_FILES['batch-fl']['error'] == 4) {
			$this->_returnErrorMsg('上传的数据不能为空!');
		}
		$fileLocation = $_FILES['batch-fl']['tmp_name'];
		if (!file_exists($fileLocation)) {
			$this->_returnErrorMsg('文件不存在!');
		} elseif (!($handle = fopen($fileLocation, 'r'))) {
			$this->_returnErrorMsg('文件无法读取!');
		}

		// 定义产品的状态
		$textData = array(
			0 => '下架',
			1 => '启用',
			2 => '缺货'
		);

		// 设置超时
		set_time_limit(0);

		// 丢弃第一行数据
		fgetcsv($handle);
		$rowNum = 1;

		// 初始化统计数据
		$success = 0;
		$fail    = 0;

		// Model
		$productModel = $this->_loadModel('product');
		while ($row = fgetcsv($handle)) {
			$rowNum++;
			$row = array_map('trim', $row); // 去除数组前后空格
			$sku = $row[0];                 // 加载数据

			$data  = array('status' => $status);
			$where = array(
				'sku'      => array('eq', $sku),
				'group_id' => array('eq', $groupId)
			);
			if ($productModel->updateByWhere($data, $where)) {
				$success++;
			} else {
				$result['msg'][] = sprintf('第%s行出错，产品:%s%s失败', $rowNum, $sku, $textData[$status]);
				$fail++;
			}
		}
		$result['msg'][] = sprintf('批量%s:总计%s条，成功%s条，失败%s条', $textData[$status], $success + $fail, $success, $fail);
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      产品首页(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function productAction()
	{
		$groupId  	= isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		if ($groupId < 1) {
			die('非法访问');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId, 'group_id, name, attributes, options, prices');
		if (empty($group)) {
			die('非法访问');
		}

		// 获取分组的价格分配，属性分配，选项分配
		$data['group_id']     = $group['group_id'];
		$data['name']         = $group['name'];
		$data['options[]']    = empty($group['options']) ? array() : explode(',', trim($group['options']));
		$data['attributes[]'] = empty($group['attributes']) ? array() : explode(',', trim($group['attributes']));
		$data['prices[]']     = empty($group['prices']) ? array() : explode(',', trim($group['prices']));

		// 从字典中获取产品属性列表
		$attributeList   = array();
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (!empty($data['attributes[]'])) {
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $data['attributes[]'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'dictionary_id, name'
			);
			$attributeList   = $dictionaryModel->getAllList($option);
		}

		// 从字典中获取产品价格列表
		$priceList = array();
		if (!empty($data['prices[]'])) {
			// 获取产品价格
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $data['prices[]'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'dictionary_id, name'
			);
			$priceList = $dictionaryModel->getAllList($option);
		}

		// 从product_option(value)表中获取产品的属性(值)列表
		$optionList      = array();
		$optionValueList = array();
		if (!empty($data['options[]'])) {
			// 获取该分组的产品选项
			$option = array(
				'where' => array(
					'option_id' => array('in', $data['options[]'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'option_id, type, name'
			);
			$optionModel = $this->_loadModel('product/option');
			$optionList  = $optionModel->getAllList($option);

			// 获取该分组的产品选项值
			$option['col']    = 'option_value_id, option_id, name';
			$optionValueModel = $this->_loadModel('product/option/value');
			$optionValueList  = $optionValueModel->getAllList($option);
		}
		$this->_view->assign('attributeList', $attributeList);
		$this->_view->assign('priceList', $priceList);
		$this->_view->assign('optionList', $optionList);
		$this->_view->assign('optionValueList', $optionValueList);
		$this->_view->assign('group', $data);

		$this->_view->render('product/group/product');
	}

	/**
	 * Describe      产品列表(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function productListAction()
	{
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$page    = isset($_POST['page']) ? (int)$_POST['page'] : 1;
		$rows    = isset($_POST['rows']) ? (int)$_POST['rows'] : 25;

		$option  = array(
			'where' => array(
				'group_id' => array('eq', $groupId)
			),
			'order' => array('product_id' => 'DESC'),
			'col'   => 'product_id, group_id, sku, image, status, description, description_short, by_added, by_modified, date_added, date_modified'
		);

		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'sku':
					case 'status':
						if (strlen(trim($val))) {
							$option['where'][$key] = array('eq', $val);
						}
					break;
				}
			}
		}
		$productModel = $this->_loadModel('product');
		$total        = $productModel->getTotalList($option);
		$data         = $productModel->getList($page, $rows, $option);
		if ($total) {
			if (!empty($data)) {
				// 获取所有的产品product_ids集合
				$productIds = array();
				foreach ($data as $val) {
					$productIds[$val['product_id']] = $val['product_id'];
				}

				//获取产品的属性、价格、选项(值)列表
				$attributeList = $this->_getAttributeListByProductIds($productIds);         // 获取属性列表
				$priceList     = $this->_getPriceListByProductIds($productIds);             // 获取价格列表
				$optionList    = $this->_getProductOptionValueListByProductIds($productIds);// 获取选项列表

				// 插入数据
				foreach ($data as $key => $val) {
					// 获取当前产品的属性attribute信息，并插入到数组
					if (isset($attributeList[$val['product_id']])) {
						// 插入属性
						$attributes = $attributeList[$val['product_id']];
						$data[$key]['attributes'] = array();

						foreach ($attributes as $attribute) {
							$data[$key]['attributes'][] = sprintf('%s:%s', $attribute['name'], $attribute['content']);
						}
						$data[$key]['attributes'] = implode('<br />', $data[$key]['attributes']);
					}

					// 获取当前产品的价格price信息，并插入到数组
					if (isset($priceList[$val['product_id']])) {
						// 插入价格
						$prices = $priceList[$val['product_id']];
						$data[$key]['prices'] = array();

						foreach ($prices as $price) {
							$data[$key]['prices'][] = sprintf('%s:<br />原价:%s,特价:%s', $price['name'], $price['price'], $price['special_price']);
						}
						$data[$key]['prices'] = implode('<br />', $data[$key]['prices']);
					}

					// 获取当前产品的选项(值)option(value)信息，并插入到数组
					if (isset($optionList[$val['product_id']])) {
						$options = $optionList[$val['product_id']];
						$data[$key]['options'] = array();

						foreach ($options as $optionName => $option) {
							$tempOptionValueList = array();
							if (!empty($option['optionValues'])) {
								foreach ($option['optionValues'] as $k => $v) {
									if ($option['type'] == '1') {
										$tempOptionValueList[] = sprintf('%s:%s%s', $k, $v['price_prefix'], $v['price']);
									} else {
										$tempOptionValueList[] = sprintf('%s', $v);
									}
								}
								$tempOptionValueList     = implode(',', $tempOptionValueList);
								$option['required']      = $option['required'] ? '必填' : '非必填';
								$tempString              = ($option['type'] == '0') ? '%s(%s):<br/>%s' : '%s(%s):<br/>%s';
								$data[$key]['options'][] = sprintf($tempString, $optionName, $option['required'], $tempOptionValueList);
							}
						}
						$data[$key]['options'] = implode('<br />', $data[$key]['options']);
					}
				}
			}
		}
		$return = array(
			'total' => $total,
			'rows'  => $data
		);
		$this->_ajaxReturn($return);
	}

	/**
	 * Describe      添加产品(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function addProductAction()
	{
		$result                    = array('error' => false, 'msg' => array());
		$data['group_id']          = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$data['sku']               = isset($_POST['sku']) ? trim($_POST['sku']) : '';
		$data['image']             = isset($_POST['image']) ? trim($_POST['image']) : '';
		$data['description']       = isset($_POST['description']) ? trim($_POST['description']) : '';
		$data['description_short'] = isset($_POST['description_short']) ? trim($_POST['description_short']) : '';
		$data['status']            = isset($_POST['status']) ? (int)$_POST['status'] : 0;
		$data['by_added']          = $_SESSION['user_account'];
		$data['date_added']        = now();

		// 验证数据
		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($data['group_id'])) {
			$this->_returnErrorMsg('产品分组非法！');
		}
		if (empty($data['sku'])) {
			$result['error'] = true;
			$result['msg'][] = '产品型号不能为空!';
		}
		$option = array(
			'where' => array(
				'group_id' => array('eq', $data['group_id']),
				'sku'      => array('eq', $data['sku'])
			)
		);
		$productModel = $this->_loadModel('product');
		if ($productModel->getTotalList($option)) {
			$result['error'] = true;
			$result['msg'][] = '产品型号在当前产品分组下，已经存在!';
		}
		if (empty($data['image'])) {
			$result['error'] = true;
			$result['msg'][] = '产品图片路径不能为空!';
		}
		if ($data['status'] < 0 || $data['status'] > 2) {
			$result['error'] = true;
			$result['msg'][] = '产品状态错误！';
		}

		if (!$result['error']) {
			// 获取当前分组下的产品的属性列表、价格列表
			$group = $groupModel->get($data['group_id'], 'attributes, prices');

			// 将产品信息添加到product表中
			$productModel = $this->_loadModel('product');
			if ($productModel->add($data)) {
				$productId = $productModel->lastInsertId();

				// 添加产品属性attribute到product_to_attribute表中
				$attributeList = explode(',', $group['attributes']);
				if (!empty($attributeList)) {
					// 遍历attrbuteList数组，获取要插入product_to_attribute表中的数据
					$productToAttributeModel = $this->_loadModel('product/to/attribute');
					foreach ($attributeList as $val) {
						$productToAttributeData = array(
							'product_id'   => $productId,
							'group_id'     => $data['group_id'],
							'content'      => '',
							'attribute_id' => $val
						);
						if (!$productToAttributeModel->add($productToAttributeData)) {
							$result['error'] = true;
							$result['msg'][] = '产品属性( ' . $val . ' )，添加失败！';
						}
					}
				}

				// 添加产品价格price到product_to_price表中
				$priceList = explode(',', $group['prices']);
				if (!empty($priceList)) {
					// 遍历priceList数组，获取要插入product_to_price表中的数据
					$productToPriceModel = $this->_loadModel('product/to/price');
					foreach ($priceList as $val) {
						$productToPriceData = array(
							'product_id'    => $productId,
							'group_id'      => $data['group_id'],
							'price_id'      => $val,
							'price'         => '0.00',
							'special_price' => '0.00',
						);
						if (!$productToPriceModel->add($productToPriceData)) {
							$result['error'] = true;
							$result['msg'][] = '产品价格( ' . $val . ' )，添加失败！';
						}
					}
				}
				if (!$result['error']) {
					$result['msg'][] =  '产品信息，添加成功！';
				}
			} else {
				$result['error'] = true;
				$result['msg'][] = '产品信息，添加失败!';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取产品信息（用于修改产品页面，产品信息展示）
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function getProductAction()
	{
		$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

		$productModel = $this->_loadModel('product');
		$data = $productModel->get($productId, 'group_id, sku, image, status, description, description_short');

		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      修改产品(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateProductAction()
	{
		$result                    = array('error' => false, 'msg' => array());
		$productId                 = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
		$groupId                   = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$data['sku']               = isset($_POST['sku']) ? trim($_POST['sku']) : '';
		$data['image']             = isset($_POST['image']) ? trim($_POST['image']) : '';
		$data['description']       = isset($_POST['description']) ? trim($_POST['description']) : '';
		$data['description_short'] = isset($_POST['description_short']) ? trim($_POST['description_short']) : '';
		$data['status']            = isset($_POST['status']) ? (int)$_POST['status'] : 0;
		$data['by_modified']       = $_SESSION['user_account'];
		$data['date_modified']     = now();

		$productModel = $this->_loadModel('product');
		if (!$productModel->vNumberId($productId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		if (empty($data['sku'])) {
			$result['error'] = true;
			$result['msg'][] = '产品型号不能为空!';
		}
		$option = array(
			'where' => array(
				'group_id'   => array('eq', $groupId),
				'sku'        => array('eq', $data['sku']),
				'product_id' => array('neq', $productId)
			)
		);
		$productModel = $this->_loadModel('product');
		if ($productModel->getTotalList($option)) {
			$result['error'] = true;
			$result['msg'][] = '产品型号在当前产品分组下，已经存在!';
		}
		if (empty($data['image'])) {
			$result['error'] = true;
			$result['msg'][] = '图片路径不能为空!';
		}
		if ($data['status'] < 0 || $data['status'] > 2) {
			$result['error'] = true;
			$result['msg'][] = '产品状态非法!';
		}

		if (!$result['error']) {
			if ($productModel->update($data, $productId)) {
				$result['msg'][] = '产品信息，修改成功！';
			} else {
				$result['error'] = true;
				$result['msg'][] = '产品信息，修改失败！';
			}
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改产品的属性值(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateAttributeValueAction()
	{
		$result        = array('error' => false, 'msg' => array());
		$groupId       = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$productId     = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
		$attributeList = empty($_POST) ? array() : $_POST;

		$productModel = $this->_loadModel('product');
		if ($groupId < 1 || !$productModel->vNumberId($productId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId);
		if (empty($group)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 从字典中获取当前group_id下的所有的产品可用属性集合
		$dictionaryAttributeList = array();
		if (!empty($group['attributes'])) {
			$attributeModel = $this->_loadModel('system/dictionary');
			$option = array(
				'where' => array(
					'type'          => array('eq', '产品属性'),
					'status'        => array('eq', 1),
					'dictionary_id' => array('in', $group['attributes'])
				),
				'col' => 'dictionary_id, name'
			);
			$dictionaryAttributeList = $attributeModel->getPairs($option);
		}

		// 删除当前商品在产品-属性product_to_attribute表中的所有的原始属性信息
		$where = array(
			'product_id' => array('eq', $productId)
		);
		$productToAttributeModel = $this->_loadModel('product/to/attribute');
		$productToAttributeModel->delByWhere($where);

		// 更新产品product表中的数据
		$productData = array(
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now(),
		);
		$productModel->update($productData, $productId);

		// 遍历产品属性集合，拼接插入表中的数据
		if (!empty($attributeList) && !empty($dictionaryAttributeList)) {
			// 遍历产品属性，拼装要插入product_to_attribute表中的数据
			foreach ($dictionaryAttributeList as $key => $val) {
				// 验证属性attribute_id是否正确
				if (isset($attributeList[$key])) {
					$data = array(
						'product_id'   => $productId,
						'group_id'     => $groupId,
						'attribute_id' => $key,
						'content'      => $attributeList[$key]
					);
					if (!$productToAttributeModel->add($data)) {
						$result['error'] = true;
						$result['msg'][] = '产品属性(' . $val . ')，保存失败！';
					}
				}
			}
		}
		if (!$result['error']) {
			$result['msg'][] = '产品属性信息，保存成功！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      修改产品的价格值(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updatePriceValueAction()
	{
		$result    = array('error' => false, 'msg' => array());
		$groupId   = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
		$priceList = empty($_POST) ? array() : $_POST;

		// 验证数据
		$productModel = $this->_loadModel('product');
		if ($groupId < 1 || !$productModel->vNumberId($productId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId);
		if (empty($group)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 从字典中获取当前group_id下的所有的产品可用价格集合
		$dictionaryPriceList = array();
		if (!empty($group['prices'])) {
			$attributeModel = $this->_loadModel('system/dictionary');
			$option = array(
				'where' => array(
					'type'          => array('eq', '产品价格'),
					'status'        => array('eq', 1),
					'dictionary_id' => array('in', $group['prices'])
				),
				'col' => 'dictionary_id, name'
			);
			$dictionaryPriceList = $attributeModel->getPairs($option);
		}

		// 遍历产品属性集合，拼接插入表中的数据
		$productToPriceData = array();
		if (!empty($priceList) && !empty($dictionaryPriceList)) {
			// 遍历产品属性，拼装要插入product_to_attribute表中的数据
			foreach ($dictionaryPriceList as $key => $value) {
				if (isset($priceList[$key . '-price']) && isset($priceList[$key . '-special_price'])) {
					// 验证属性price_id是否有效
					if ($priceList[$key . '-price'] == 0) {
						$result['error'] = true;
						$result['msg'][] = '产品原价不能为0！';
					}
					if ($priceList[$key . '-price'] <= $priceList[$key . '-special_price']) {
						$result['error'] = true;
						$result['msg'][] = '产品原价必须大于产品特价！';
					}
					if ($result['error']) {
						$this->_ajaxReturn($result);
					}

					$productToPriceData[$value] = array(
						'product_id'    => $productId,
						'group_id'      => $groupId,
						'price_id'      => $key,
						'price'         => $priceList[$key . '-price'],
						'special_price' => $priceList[$key . '-special_price'],
					);
				}
			}
		}
		// 更新产品product表中的数据
		$priceData = array(
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now(),
		);
		$productModel->update($priceData, $productId);

		// 删除当前商品在产品-价格product_to_price表中所有的原始价格price信息
		$where = array(
			'product_id' => array('eq', $productId)
		);
		$productToPriceModel = $this->_loadModel('product/to/price');
		$productToPriceModel->delByWhere($where);

		// 循环遍历价格信息，插入到product_to_price表中
		if (!empty($productToPriceData)) {
			foreach ($productToPriceData as $key => $val) {
				if (!$productToPriceModel->add($val)) {
					$result['error'] = true;
					$result['msg'][] = '产品价格(' . $key . ')，保存失败！';
				}
			}
		}
		if (!$result['error']) {
			$result['msg'][] = '产品价格信息，保存成功！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      更新产品选项值(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function updateProductToOptionValueAction()
	{
		$result          = array('error' => false, 'msg' => array());
		$groupId         = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$productId       = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

		// options       ：产品选项的option_id(数组类型：option_id集合)(已选择)
		// optionRequires：产品选项是否是必需值(数组类型：option_id集合)(已选择)
		// $optionValues ：产品选项值的option_value_id(数组类型：key为option_id，value为option_value_id组成的数组)(已选择)
		// $optionvalues = array('option_id' => array('0' => 'option_value_id1', '1' => 'option_value_id1'));
		// $optionvalue  ：产品选项值的选项值(数组类型：key为option_id，value为price和price_prefix两个字段组成的数组)(全部)
		// $optionvalue = array('option_id' => array('option_value_id'=> array('price_prefix' => '价格前缀', 'price' => '价格')));
		$options         = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : array();
		$optionRequireds = isset($_POST['option_requireds']) && is_array($_POST['option_requireds']) ? $_POST['option_requireds'] : array();
		$optionValues    = isset($_POST['option_values']) && is_array($_POST['option_values']) ? $_POST['option_values'] : array();
		$optionvalue     = isset($_POST['option_value']) && is_array($_POST['option_value']) ? $_POST['option_value'] : array();

		// 验证有效性
		$productModel = $this->_loadModel('product');
		if ($groupId < 1 || !$productModel->vNumberId($productId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId);
		if (empty($group)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 删除当前产品procut_id在product_to_option表中的关系数据
		$productToOptionModel = $this->_loadModel('product/to/option');
		$productToOptionModel->del($productId);

		// 删除当前产品procut_id在product_to_option_value表中的关系数据
		$productToOptionValueModel = $this->_loadModel('product/to/option/value');
		$productToOptionValueModel->del($productId);

		// 更新产品product表中的数据
		$dataModified = array(
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now(),
		);
		$productModel->update($dataModified, $productId);

		// 从product_option表中获取当前group_id下所有的options集合
		$optionTypeList = array();
		if (!empty($group['options'])) {
			$option = array(
				'where' => array(
					'option_id' => array('in', $group['options'])
				),
				'col' => 'option_id, type, name'
			);
			$optionModel    = $this->_loadModel('product/option');
			$optionTypeList = $optionModel->getPairs2($option);
		}

		// 初始化插入数据库中的数据
		if (!empty($options) && !empty($optionTypeList)) {
			foreach ($optionTypeList as $key => $value) {
				if (in_array($key, $options) && ($value['type'] == '0' || ($value['type'] == '1' && isset($optionValues[$key]) && is_array($optionValues[$key])))) {
					$optionData = array(
						'product_id' => $productId,
						'group_id'   => $groupId,
						'option_id'  => $key,
						'required'   => in_array($key, $optionRequireds) ? '1' : '0',
					);

					// 添加产品选项信息到product_to_option表中
					if ($productToOptionModel->add($optionData)) {
						// 判断选项值是输入类型(0)/选择类型(1)
						// 如果选项值是选择类型，获取当前option_id下的已经选择的option_value_id集合
						$optionValuesList = $value['type'] == '1' ? $optionValues[$key] : array('0');

						if (!empty($optionValuesList))  {
							// 遍历数组，获取插入到product_to_option_value表中的数据
							foreach ($optionValuesList as $val) {
								$optionValueList = isset($optionvalue[$key][$val]) && is_array($optionvalue[$key][$val]) ? $optionvalue[$key][$val] : array();
								$optionValueData = array(
									'product_id'      => $productId,
									'group_id'        => $groupId,
									'option_id'       => $key,
									'option_value_id' => $val,
									'price_prefix'    => isset($optionValueList['price_prefix']) && in_array($optionValueList['price_prefix'], array('+', '-')) ? $optionValueList['price_prefix'] : '+',
									'price'           => isset($optionValueList['price']) ? $optionValueList['price'] : '0.00'
								);
								if (!$productToOptionValueModel->add($optionValueData)) {
									$result['error'] = true;
									$result['msg'][] = '产品选项值(' . $val . ')，保存失败！';
								}
							}
						}
					} else {
						$result['error'] = true;
						$result['msg'][] = '产品选项(' . $value['name'] . ')，保存失败！';
					}
				}
			}
		}
		if (!$result['error']) {
			$result['msg'][] = '产品选项和选项值数据，保存成功！';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      批量格式化(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function formatAction()
	{
		$result  = array('error' => false, 'msg' => array());
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		$groupModel = $this->_loadModel('product/group');
		if (!$groupModel->vNumberId($groupId)) {
			$this->_returnErrorMsg('非法操作！');
		}
		if (!isset($_FILES['format-fl']['error']) || $_FILES['format-fl']['error'] == 4) {
			$result['error'] = true;
			$result['msg'][] = '上传的数据不能为空!';
		}
		$fileLocation = $_FILES['format-fl']['tmp_name'];
		if (!file_exists($fileLocation)) {
			$result['error'] = true;
			$result['msg'][] = '文件不存在!';
		} elseif (!($handle = fopen($fileLocation, "r"))) {
			$result['error'] = true;
			$result['msg'][] = '文件无法读取!';
		}

		if (!$result['error']) {
			// 修改配置参数
			set_time_limit(0);
			@ini_set('post_max_size', '100M');
			@ini_set('upload_max_filesize', '100M');

			// 丢弃第一行数据
			fgetcsv($handle);

			// 设置导出文件的路径
			$dirName = 'productFormat' . time();
			Titan::mkdir(VAR_PATH . "download/{$dirName}/");

			// 生成CSV表
			$productFileName   = 'product.csv';
			$productFile       = fopen(VAR_PATH . "download/{$dirName}/{$productFileName}", 'a');
			$attributeFileName = 'attribute.csv';
			$attributeFile     = fopen(VAR_PATH . "download/{$dirName}/{$attributeFileName}", 'a');
			$priceFileName     = 'price.csv';
			$priceFile         = fopen(VAR_PATH . "download/{$dirName}/{$priceFileName}", 'a');
			$optionFileName    = 'option.csv';
			$optionFile        = fopen(VAR_PATH . "download/{$dirName}/{$optionFileName}", 'a');
			fputcsv($productFile, array('产品型号', '图片路径', '状态', '简介', '描述'));
			fputcsv($attributeFile, array('产品型号', '属性1', '属性2', '属性3', '属性4', '属性5', '属性6', '属性7', '属性8', '属性9'));
			fputcsv($priceFile, array('产品型号', '原价', '特价'));
			fputcsv($optionFile, array('产品型号', '选项1:必填', '选项1:内容', '选项2:必填', '选项2:内容', '选项3:必填', '选项3:内容', '选项4:必填', '选项4:内容'));

			while ($row = fgetcsv($handle)) {
				$row = array_map('trim', $row);
				fputcsv($productFile, array($row[0], $row[4], $row[12], $row[2], $row[3]));
				fputcsv($attributeFile, array($row[0], $row[20], $row[21], $row[22], $row[23], $row[24], $row[25], $row[26], $row[27], $row[28]));
				fputcsv($priceFile, array($row[0], $row[7], $row[8]));
				$optionData  = array();
				$optionValue = $row[6];

				if (!empty($optionValue)) {
					$optionData[] = $row[0];
					// 分割选项
					$optionValueArr = explode(';', $optionValue);

					if (!empty($optionValueArr)) {
						foreach ($optionValueArr as $val) {
							// 分割选项名称和选项值
							$arr1 = explode('#', $val);
							$optionData[] = '1';

							if (isset($arr1[1])) {
								// 分割选项值
								$arr2 = explode(':', $arr1[1]);
								$arr3 = array();
								foreach ($arr2 as $val1) {
									$arr3[] = $val1 . ',+,0';
								}
								$optionData[] = implode(';', $arr3);
							} else {
								$optionData[] = '0,+,0';
							}
						}
					}
					fputcsv($optionFile, $optionData);
				}
			}
			fclose($productFile);
			fclose($attributeFile);
			fclose($priceFile);
			fclose($optionFile);

			// 压缩CSV文件
			$objZipArchive = new ZipArchive();
			if (true == $objZipArchive->open(VAR_PATH . "download/{$dirName}.zip", ZipArchive::OVERWRITE)) {
				// 第一个变量:路径+文件名,第二个变量:文件名，若无第二个变量,将压缩整个路径的文件夹
				$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$productFileName}", $productFileName);
				$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$attributeFileName}", $attributeFileName);
				$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$priceFileName}", $priceFileName);
				$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$optionFileName}", $optionFileName);
				$objZipArchive->close();
			}

			// 删除文件
			@unlink(VAR_PATH . "download/{$dirName}/{$productFileName}");
			@unlink(VAR_PATH . "download/{$dirName}/{$attributeFileName}");
			@unlink(VAR_PATH . "download/{$dirName}/{$priceFileName}");
			@unlink(VAR_PATH . "download/{$dirName}/{$optionFileName}");
			@rmdir(VAR_PATH  . "download/{$dirName}");

			$result['url']   = APP_HTTP . "Var/download/{$dirName}.zip";
			$result['msg'][] = '导出成功';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      导入产品(产品product页面)
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */
	public function uploadProductAction()
	{
		$result   = array('error' => false, 'msg' => array());
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		if ($groupId < 1) {
			$this->_returnErrorMsg('非法操作！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId);
		if (empty($group)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 设置超时
		set_time_limit(0);
		$errorNum = 0;  // 定义并初始化错误行数

		// 添加上传产品数据
		$resultHandle = $this->_validateProductUpload('product');
		$result       = array_merge($result, $resultHandle['result']);
		if (!$result['error']) {
			// 丢弃第一行
			$productHandle = $resultHandle['handle'];
			fgetcsv($productHandle);
			$rowNum   = 1;  // 定义并初始化行数

			// 获取产品product表中，当前产品分组group_id下的所有产品的信息
			$option = array(
				'where' => array(
					'group_id' => array('eq', $groupId)
				),
				'order' => array('product_id' => 'DESC'),
				'col'   => 'sku, product_id'
			);
			$productModel = $this->_loadModel('product');
			$productArr   = $productModel->getPairs($option);

			while ($productRow = fgetcsv($productHandle)) {
				$rowNum++;
				$productRow = array_map('trim', $productRow);

				// 获取上传数据
				$data['sku']               = $productRow[0];
				$data['image']             = $productRow[1];
				$data['status']            = $productRow[2] == 1 ? 1 : 0;
				$data['description_short'] = $productRow[3];
				$data['description']       = $productRow[4];

				// 验证数据
				$productIsNew = true; // 默认产品执行添加操作
				if (empty($data['sku'])) {
					$errorNum++;
					$result['error'] = true;
					$result['msg'][] = sprintf('产品表第%s行产品型号不能为空', $rowNum);
				} elseif (isset($productArr[$data['sku']])) {
					// 如果产品product表中存在sku，则执行更新操作，否则执行添加操作
					$productIsNew = false;
					$productId    = $productArr[$data['sku']];
				}
				if (empty($data['image'])) {
					$errorNum++;
					$result['error'] = true;
					$result['msg'][] = sprintf('产品表第%s行图片路径不能为空', $rowNum);
				}
				if ($result['error']) continue;

				if ($productIsNew) {
					// 添加产品
					$data['group_id']   = $groupId;
					$data['by_added']   = $_SESSION['user_account'];
					$data['date_added'] = now();

					if (!$productModel->add($data)) {
						$errorNum++;
						$result['msg'][] = sprintf('产品表第%s行产品型号:%s添加失败', $rowNum, $data['sku']);
						continue;
					}
					// 将新添加的产品product_id加入到$productArr数组中，防止重复添加
					$productArr[$data['sku']] = $productModel->lastInsertId();
				} else {
					// 更新产品
					$data['by_modified']   = $_SESSION['user_account'];
					$data['date_modified'] = now();

					if (!$productModel->update($data, $productId)) {
						$errorNum++;
						$result['msg'][] = sprintf('产品表第%s行产品型号:%s添加失败', $rowNum, $data['sku']);
					}
				}
			}
		}

		// 添加产品属性
		$resultHandle    = $this->_validateProductUpload('attribute');
		$attributeResult = $resultHandle['result'];
		$result['error'] = $attributeResult['error'];
		if (!empty($attributeResult['msg'])) {
			foreach ($attributeResult['msg'] as $val) {
				$result['msg'][] = $val;
			}
		}

		if (!$result['error']) {
			// 丢弃第一行
			$attributeHandle = $resultHandle['handle'];
			fgetcsv($attributeHandle);
			$rowNum = 1;    // 定义并初始化行数

			// 从字典中获取当前产品分组group_id下的所有的产品属性attribute列表（status字段原因）
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $group['attributes']),
					'type'          => array('eq', '产品属性'),
					'status'        => array('eq', 1)
				),
				'col' => 'dictionary_id, name'
			);
			$dictionaryModel      = $this->_loadModel('system/dictionary');
			$productAttributeList = $dictionaryModel->getPairs($option);

			if (!empty($productAttributeList)) {
				while ($attributeRow = fgetcsv($attributeHandle)) {
					$rowNum++;
					$attributeRow    = array_map('trim', $attributeRow);
					$attributeColumn = 1;
					$sku             = $attributeRow[0];

					// 验证数据
					if (empty($sku)) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('属性表第%s行产品型号不能为空！', $rowNum);
					} elseif (!isset($productArr[$sku])) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('属性表第%s行产品型号:%s不存在！', $rowNum, $sku);
					}
					if ($result['error']) continue;

					// 当前产品product_id在产品-属性product_to_attribute表中的原始属性信息
					$where = array(
						'product_id' => array('eq', $productArr[$sku])
					);
					$productToAttributeModel = $this->_loadModel('product/to/attribute');
					$productToAttributeModel->delByWhere($where);

					// 遍历产品属性列表，拼接要插入的数据(逐条插入)
					foreach ($productAttributeList as $val) {
						// 添加产品属性
						$data = array(
							'product_id'   => $productArr[$sku],
							'group_id'     => $groupId,
							'attribute_id' => $val,
							'content'      => $attributeRow[$attributeColumn++]
						);
						if (!$productToAttributeModel->add($data)) {
							$errorNum++;
							$result['msg'][] = sprintf('属性表第%s行产品型号:%s的属性添加失败', $rowNum, $sku);
						}
					}
				}
			}
		}

		// 添加产品价格
		$resultHandle    = $this->_validateProductUpload('price');
		$priceResult     = $resultHandle['result'];
		$result['error'] = $priceResult['error'];
		if (!empty($priceResult['msg'])) {
			foreach ($priceResult['msg'] as $val) {
				$result['msg'][] = $val;
			}
		}

		if (!$result['error']) {
			// 丢弃第一行
			$priceHandle = $resultHandle['handle'];
			fgetcsv($priceHandle);
			$rowNum = 1;

			// 从字典中获取当前产品分组group_id下的所有的产品价格price列表（status字段原因）
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $group['prices']),
					'type'          => array('eq', '产品价格'),
					'status'        => array('eq', 1)
				),
				'col' => 'dictionary_id, name'
			);
			$productPriceList = $dictionaryModel->getPairs($option);
			if (!empty($productPriceList)) {
				while ($priceRow = fgetcsv($priceHandle)) {
					$rowNum++;
					$priceRow    = array_map('trim', $priceRow);
					$priceColumn = 1;
					$sku         = $priceRow[0];

					// 验证数据
					if (empty($sku)) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('价格表第%s行产品型号不能为空！', $rowNum);
					} elseif (!isset($productArr[$sku])) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('价格表第%s行产品型号:%s不存在！', $rowNum, $sku);
					}
					if ($result['error']) continue;

					// 删除产品的价格关系
					$where = array(
						'product_id' => array('eq', $productArr[$sku])
					);
					$productToPriceModel = $this->_loadModel('product/to/price');
					$productToPriceModel->delByWhere($where);

					// 遍历产品的价格price列表，拼接插入的数据(逐条插入)
					foreach ($productPriceList as $val) {
						// 添加产品价格
						$data = array(
							'product_id'    => $productArr[$sku],
							'group_id'      => $groupId,
							'price_id'      => $val,
							'price'         => $priceRow[$priceColumn++],
							'special_price' => $priceRow[$priceColumn++]
						);
						if (!$productToPriceModel->add($data)) {
							$errorNum++;
							$result['msg'][] = sprintf('价格表第%s行产品型号:%s的价格添加失败', $rowNum, $sku);
						}
					}
				}
			}
		}

		// 添加产品选项(值)
		$resultHandle    = $this->_validateProductUpload('option');
		$optionResult    = $resultHandle['result'];
		$result['error'] = $optionResult['error'];
		if (!empty($optionResult['msg'])) {
			foreach ($optionResult['msg'] as $val) {
				$result['msg'][] = $val;
			}
		}

		if (!$result['error']) {
			if (!empty($group['options'])) {
				// 从product_option表中获取当前分组已分配的选项option集合
				$option = array(
					'where' => array(
						'option_id' => array('in', $group['options'])
					),
					'order' => array('sort' => 'ASC'),
					'col'   => 'option_id, type, name'
				);
				$productOptionModel = $this->_loadModel('product/option');
				$optionList = $productOptionModel->getAllList($option);

				// 从product_option_value表中获取当前分组已分配的选项值option_value集合
				$option = array(
					'where' => array(
						'option_id' => array('in', $group['options'])
					),
					'order' => array('sort' => 'ASC'),
					'col'   => 'option_value_id, option_id, name'
				);
				$productOptionValueModel = $this->_loadModel('product/option/value');
				$tempList = $productOptionValueModel->getAllList($option);
				$optionValueList  = array();
				foreach ($tempList as $val) {
					$optionValueList[$val['option_id']][$val['name']] = $val['option_value_id'];
				}

				// 丢弃第一行数据
				$optionHandle = $resultHandle['handle'];
				fgetcsv($optionHandle);
				$rowNum = 1;

				while ($optionRow = fgetcsv($optionHandle)) {
					$rowNum++;
					$optionRow = array_map('trim', $optionRow);
					$sku       = $optionRow[0];

					// 验证数据
					if (empty($sku)) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('选项表第%s行产品型号不能为空', $rowNum);
					} elseif (!isset($productArr[$sku])) {
						$errorNum++;
						$result['error'] = true;
						$result['msg'][] = sprintf('选项表第%s行产品型号:%s不存在', $rowNum, $sku);
					}
					if ($result['error']) continue;

					// 从product_to_option和product_to_option_value表中删除产品关系
					$where = array(
						'product_id' => array('eq', $productArr[$sku])
					);
					// 删除产品选项关系
					$productToOptionModel = $this->_loadModel('product/to/option');
					$productToOptionModel->delByWhere($where);

					// 删除产品选项值关系
					$prodcutToOptionValueModel = $this->_loadModel('product/to/option/value');
					$prodcutToOptionValueModel->delByWhere($where);

					// 添加产品选项关系和产品选项值关系
					if (!empty($optionList)) {
						foreach ($optionList as $key => $val) {
							$count    = $key * 2 + 1;
							$required = isset($optionRow[$count]) ? $optionRow[$count] : '';
							$values   = isset($optionRow[$count + 1]) ? $optionRow[$count + 1] : '';

							if ($required == '') continue;

							// 添加产品选项关系
							$data = array(
								'product_id' => $productArr[$sku],
								'group_id'   => $groupId,
								'option_id'  => $val['option_id'],
								'required'   => $required == 1 ? 1 : 0
							);
							if ($productToOptionModel->add($data)) {
								if ($val['type'] == 1) {
									// 选择性选项
									if (!isset($optionValueList[$val['option_id']])) {
										$errorNum++;
										$result['msg'][] = sprintf('选项:%s不存在选项值', $val['name']);
										continue;
									}
									$valueArr = $optionValueList[$val['option_id']];
									$arr1     = explode(';', $values);
								} else {
									// 填入性选项
									$valueArr = array('0' => '0');
									$arr1     = array($values);
								}

								// 添加产品选项值关系
								$pricePrefixArr = array('+', '-', '*');
								foreach ($arr1 as $k => $v) {
									$arr2 = explode(',', $v);
									if (!isset($arr2[0]) || !isset($valueArr[$arr2[0]])) {
										$errorNum++;
										$result['msg'][] = sprintf('选项表第%s行的选项%s的第%s个选项值不存在', $rowNum, $val['name'], $k + 1);
										continue;
									}
									$data = array(
										'product_id'      => $productArr[$sku],
										'group_id'        => $groupId,
										'option_id'       => $val['option_id'],
										'option_value_id' => $valueArr[$arr2[0]],
										'price'           => (isset($arr2[2]) && $arr2[2] > 0) ? $arr2[2] : '0',
										'price_prefix'    => (isset($arr2[1]) && in_array($arr2[1], $pricePrefixArr)) ? $arr2[1] : '+'
									);
									if (!$prodcutToOptionValueModel->add($data)) {
										$errorNum++;
										$result['msg'][] = sprintf('选项表第%s行的选项:%s的第%s个选项值添加失败', $rowNum, $val['name'], $k + 1);
									}
								}
							} else {
								$errorNum++;
								$result['msg'][] = sprintf('选项表第%s行的选项:%s添加失败', $rowNum, $val['name']);
							}
						}
					}
				}
			}
		}
		if (!$errorNum) {
			$result['msg'][] = '产品导入成功！';
		} else {
			$result['error'] = true;
			array_unshift($result['msg'], '产品导入失败！');
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      事例下载
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 */

	public function exampleAction()
	{
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

		// 验证数据
		if ($groupId < 1) {
			$this->_returnErrorMsg('非法操作！');
		}
		$groupModel = $this->_loadModel('product/group');
		$group      = $groupModel->get($groupId);
		if (empty($group)) {
			$this->_returnErrorMsg('非法操作！');
		}

		// 初始化产品属性列表数据
		$attributeList   = array();
		$dictionaryModel = $this->_loadModel('system/dictionary');
		if (!empty($group['attributes'])) {
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $group['attributes'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'type, name'
			);
			$attributeList   = $dictionaryModel->getAllList($option);
		}

		// 初始化产品价格列表数据
		$priceList = array();
		if (!empty($group['price'])) {
			$option = array(
				'where' => array(
					'dictionary_id' => array('in', $group['price'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'type, name'
			);
			$priceList = $dictionaryModel->getAllList($option);
		}

		// 初始化产品选项列表数据
		$optionList = array();
		if (!empty($group['options'])) {
			$option = array(
				'where' => array(
					'option_id' => array('in', $group['options'])
				),
				'order' => array('sort' => 'ASC'),
				'col'   => 'type, name'
			);
			$optionModel = $this->_loadModel('product/option');
			$optionList  = $optionModel->getAllList($option);
		}

		// 设置导出文件的路径
		$dirName = 'product' . time();
		Titan::mkdir(VAR_PATH . "download/{$dirName}/");

		set_time_limit(0);
		// 创建PHPExcel对象,生成CSV表
		$productFileName   = 'product.csv';
		$productFile       = fopen(VAR_PATH . "download/{$dirName}/{$productFileName}", 'a');
		$attributeFileName = 'attribute.csv';
		$attributeFile     = fopen(VAR_PATH . "download/{$dirName}/{$attributeFileName}", 'a');
		$priceFileName     = 'price.csv';
		$priceFile         = fopen(VAR_PATH . "download/{$dirName}/{$priceFileName}", 'a');
		$optionFileName    = 'option.csv';
		$optionFile        = fopen(VAR_PATH . "download/{$dirName}/{$optionFileName}", 'a');

		fputcsv($productFile, array('产品型号', '图片路径', '状态', '排序', '简介', '描述'));
		$attributeArr[] = '产品型号';
		if (!empty($attributeList)) {
			foreach ($attributeList as $val) {
				$attributeArr[] = $val['name'];
			}
		}

		fputcsv($attributeFile, $attributeArr);
		$priceArr[] = '产品型号';
		if (!empty($priceList)) {
			foreach ($priceList as $val) {
				$priceArr[] = $val['name'] . '原价';
				$priceArr[] = $val['name'] . '特价';
			}
		}

		fputcsv($priceFile, $priceArr);
		$optionArr[] = '产品型号';
		if (!empty($optionList)) {
			foreach ($optionList as $val) {
				$optionArr[] = $val['name'] . '必填';
				$optionArr[] = $val['name'];
			}
		}
		fputcsv($optionFile, $optionArr);
		fclose($productFile);
		fclose($attributeFile);
		fclose($priceFile);
		fclose($optionFile);

		// 压缩CSV文件
		$objZipArchive = new ZipArchive();
		if (true == $objZipArchive->open(VAR_PATH . "download/{$dirName}.zip", ZipArchive::OVERWRITE)) {
			// 第一个变量:路径+文件名,第二个变量:文件名\，若无第二个变量,将压缩整个路径的文件夹
			$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$productFileName}", $productFileName);
			$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$attributeFileName}", $attributeFileName);
			$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$priceFileName}", $priceFileName);
			$objZipArchive->addFile(VAR_PATH . "download/{$dirName}/{$optionFileName}", $optionFileName);
			$objZipArchive->close();
		}

		// 删除文件
		@unlink(VAR_PATH . "download/{$dirName}/{$productFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$attributeFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$priceFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$optionFileName}");
		@rmdir(VAR_PATH . "download/{$dirName}");

		$return = array(
			'url' => APP_HTTP . "Var/download/{$dirName}.zip"
		);
		$this->_ajaxReturn($return);
	}

	/**
	 * Describe      根据产品product_ids获取属性attribute列表
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 * @param $productIds
	 * @return array
	 */
	private function _getAttributeListByProductIds($productIds)
	{
		if (empty($productIds) || !is_array($productIds)) {
			return array();
		}

		// 根据产品product_ids从product_to_attribute表中获取所有的产品属性列表
		$option = array(
			'col'   => 'product_id, attribute_id, content',
			'where' => array(
				'product_id' => array('in', $productIds)
			)
		);
		$productToAttributeModel = $this->_loadModel('product/to/attribute');
		$productToAttributeList  = $productToAttributeModel->getAllList($option);
		if (empty($productToAttributeList)) {
			return array();
		}

		// 获取所有的产品的属性attribute_ids集合
		$attributeIds = array();
		foreach ($productToAttributeList as $val) {
			$attributeIds[$val['attribute_id']] = $val['attribute_id'];
		}

		// 从字典中获取当前attribute_ids集合下的所有的属性attribute列表
		$option = array(
			'where' => array(
				'dictionary_id' => array('in', $attributeIds),
				'type'          => array('eq', '产品属性'),
				'status'        => array('eq', 1)
			),
			'col' => 'dictionary_id, name',
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$attributeList   = $dictionaryModel->getPairs($option);
		if (empty($attributeList)) {
			return array();
		}

		// 遍历产品，重新拼接产品的属性信息列表
		$data = array();
		foreach ($productToAttributeList as $val) {
			if (isset($attributeList[$val['attribute_id']])) {
				$data[$val['product_id']][$val['attribute_id']] = array(
					'name'    => $attributeList[$val['attribute_id']],
					'content' => $val['content']
				);
			}
		}
		return $data;
	}

	/**
	 * Describe      根据产品product_ids集合获取产品价格price列表
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 * @param $productIds
	 * @return array
	 */
	private function _getPriceListByProductIds($productIds)
	{
		if (empty($productIds) || !is_array($productIds)) {
			return array();
		}

		// 根据当前product_ids从产品product_to_price表中获取产品价格price_ids集合
		$option = array(
			'where' => array(
				'product_id' => array('in', $productIds)
			),
			'col' => 'product_id, price_id, price, special_price'
		);
		$productToPriceModel = $this->_loadModel('product/to/price');
		$productToPriceList  = $productToPriceModel->getAllList($option);
		if (empty($productToPriceList)) {
			return array();
		}
		$priceIds = array();
		foreach ($productToPriceList as $val) {
			$priceIds[$val['price_id']] = $val['price_id'];
		}

		// 根据当前的price_ids从字典中查找对应的产品价格列表
		$option = array(
			'where' => array(
				'dictionary_id' => array('in', $priceIds),
				'type'          => array('eq', '产品价格'),
				'status'        => array('eq', 1)
			),
			'col' => 'dictionary_id, name',
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$priceList       = $dictionaryModel->getPairs($option);
		if (empty($priceList)) {
			return array();
		}

		// 遍历产品，重新拼接产品的价格信息列表
		$data = array();
		foreach ($productToPriceList as $val) {
			if (isset($priceList[$val['price_id']])) {
				$data[$val['product_id']][$val['price_id']] = array(
					'name'          => $priceList[$val['price_id']],
					'price'         => $val['price'],
					'special_price' => $val['special_price']
				);
			}
		}
		return $data;
	}

	/**
	 * Describe      通过产品product_ids获取选项option和选项值option_value数据
	 * ByAdded       王天贵
	 * DateAdded     2016-08-12
	 * ByModified    雷泳涛
	 * DateModified  2016-10-26
	 * @param $productIds
	 * @return array|string
	 */
	private function _getProductOptionValueListByProductIds($productIds)
	{
		if (empty($productIds) || !is_array($productIds)) {
			return array();
		}

		// 根据当前产品product_ids从product_to_option中获取所有的产品选项option列表
		$option = array(
			'where' => array(
				'product_id' => array('in', $productIds)
			),
			'order' => array('option_id' => 'ASC')
		);
		$productToOptionModel = $this->_loadModel('product/to/option');
		$productToOptionList  = $productToOptionModel->getAllList($option);
		if (empty($productToOptionList)) {
			return array();
		}
		// 获取当前product_ids下的的所有的option_ids集合
		$optionIds = array();
		foreach ($productToOptionList as $val) {
			$optionIds[$val['option_id']] = $val['option_id'];
		}

		// 根据当前的option_Ids从product_option表中查找所有的选项option列表
		$option = array(
			'where' => array(
				'option_id' => array('in', $optionIds)
			),
			'col'   => 'option_id, type, name'
		);
		$optionModel = $this->_loadModel('product/option');
		$optionList  = $optionModel->getPairs2($option);
		if (empty($optionList)) {
			return array();
		}

		// 根据当前产品product_ids从product_to_option_value中获取所有的产品选项值option_value列表
		$option = array(
			'where' => array(
				'product_id' => array('in', $productIds)
			),
			'order' => array('option_id' => 'ASC', 'option_value_id' => 'ASC')
		);
		$productToOptionValueModel = $this->_loadModel('product/to/option/value');
		$productToOptionValueList  = $productToOptionValueModel->getAllList($option);

		// 获取当前product_ids下的的所有的option_value_ids集合
		$optionValueIds = array();
		if (!empty($productToOptionValueList)) {
			foreach ($productToOptionValueList as $val) {
				$optionValueIds[$val['option_value_id']] = $val['option_value_id'];
			}
		}

		// 根据当前的option_value_Ids从product_option_value表中查找所有的选项值option_value列表
		$optionValueList = array();
		if (!empty($optionValueIds)) {
			$option = array(
				'where' => array(
					'option_value_id' => array('in', $optionValueIds)
				),
				'col'   => 'option_value_id, name'
			);
			$optionValueModel = $this->_loadModel('product/option/value');
			$optionValueList  = $optionValueModel->getPairs($option);
		}

		// 遍历产品，重新拼接产品的选项(值)列表
		$data = array();
		if (!empty($productToOptionList) && !empty($productToOptionValueList)) {
			foreach ($productToOptionList as $val) {
				$data[$val['product_id']][$optionList[$val['option_id']]['name']]['required'] = $val['required'];
				$data[$val['product_id']][$optionList[$val['option_id']]['name']]['type']     = $optionList[$val['option_id']]['type'];
			}

			foreach ($productToOptionValueList as $val) {
				if (isset($data[$val['product_id']])) {
					$optionName = isset($optionList[$val['option_id']]['name']) ? $optionList[$val['option_id']]['name'] : '';
					$type       = $data[$val['product_id']][$optionName]['type'];
					if ($type) {
						$optionValueName = isset($optionValueList[$val['option_value_id']]) ? $optionValueList[$val['option_value_id']] : '';
						$data[$val['product_id']][$optionName]['optionValues'][$optionValueName]['price']        = $val['price'];
						$data[$val['product_id']][$optionName]['optionValues'][$optionValueName]['price_prefix'] = $val['price_prefix'];
					} else {
						$data[$val['product_id']][$optionName]['optionValues']['price']        = $val['price'];
						$data[$val['product_id']][$optionName]['optionValues']['price_prefix'] = $val['price_prefix'];
					}
				}
			}
		}
		return $data;
	}

	/**
	 * 验证产品上传数据的有效性
	 *
	 * @param string $uploadType
	 * @return resource
	 */
	private function _validateProductUpload($uploadType = 'product')
	{
		$type   = $uploadType . '-fl';
		$result = array('error' => false, 'msg' => array());
		$typeArray = array(
			'product'   => '产品',
			'attribute' => '产品属性',
			'price'     => '产品价格',
			'option'    => '产品选项'
		);

		if (isset($_FILES['product-fl']['error']) || $_FILES['product-fl']['error'] != 4) {
			$fileLocation = $_FILES[$type]['tmp_name'];
			if (!file_exists($fileLocation)) {
				$result['error'] = true;
				$result['msg'][] = $typeArray[$uploadType] . '文件不存在!';
			} elseif (!$handle = fopen($fileLocation, "r")) {
				$result['error'] = true;
				$result['msg'][] = $typeArray[$uploadType] . '文件无法读取!';
			}
		}
		return array(
			'result'  => $result,
			'handle'  => isset($handle) ? $handle : ''
		);
	}
}
