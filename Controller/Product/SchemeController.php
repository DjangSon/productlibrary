<?php

/**
 * Class Product_SchemeController
 * User  陈伟义
 * Date  2016-08-12
 */
class Product_SchemeController extends CustomController
{
	/**
	 * @var array
	 */
	private $_data = array();

	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/scheme/index');
	}

	/**
	 * Describe     方案管理主页
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function indexAction()
	{
		// 获取分类分组
		$option = array(
			'order' => array('sort' => 'ASC'),
			'col'   => 'group_id, name'
		);
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroupList  = $categoryGroupModel->getAllList($option);
		$this->_view->assign('categoryGroupList', $categoryGroupList);
		$this->_view->render('product/scheme/index');
	}

	/**
	 * Describe     方案列表
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function listAction()
	{
		$page        = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows        = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$option      = array('order' => array('sort' => 'ASC'));
		$schemeModel = $this->_loadModel('scheme');
		$total       = $schemeModel->getTotalList($option);
		$data        = array();
		if ($total) {
			$data             = $schemeModel->getList($page, $rows, $option);
			$categoryGroupIds = array();
			foreach ($data as $val) {
				$categoryGroupIds[] = $val['category_group_id'];
			}

			// 获取分类分组列表
			$option = array(
				'where' => array('group_id' => array('in', $categoryGroupIds)),
				'col'   => 'group_id, name'
			);
			$groupModel = $this->_loadModel('category/group');
			$groupList  = $groupModel->getPairs($option);

			// 插入分类分组名
			foreach ($data as $key => $val) {
				$ruleData                     = json_decode($val['rule_json'], true);
				$data[$key]['isset_rule']     = (isset($ruleData['categoryRule']) && isset($ruleData['productRule']) && isset($ruleData['optionRule']) && isset($ruleData['priceRule'])) ? '1' : '0';
				$data[$key]['category_group'] = isset($groupList[$val['category_group_id']]) ? $groupList[$val['category_group_id']] : '';
			}
		}
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	/**
	 * Describe     添加方案
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function schemeAddAction()
	{
		// 加载数据
		$data['category_group_id'] = isset($_POST['category_group_id']) ? (int)$_POST['category_group_id'] : 0;
		$data['name']              = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['sort']              = (isset($_POST['sort']) && $_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['remarks']           = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
		$data['by_added']          = $_SESSION['user_account'];
		$data['date_added']        = now();
		$result                    = array('error' => false, 'msg' => array());

		// 验证数据
		$groupModel  = $this->_loadModel('category/group');
		$schemeModel = $this->_loadModel('scheme');
		if (empty($data['category_group_id'])) {
			$result['error'] = true;
			$result['msg'][] = '分类分组不能为空';
		} elseif (!$groupModel->validate($data['category_group_id'])) {
			$result['error'] = true;
			$result['msg'][] = '分类分组选择有误';
		}
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分类名称不能为空';
		} elseif ($schemeModel->existName($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分类名称已存在';
		}
		if (empty($data['remarks'])) {
			$result['error'] = true;
			$result['msg'][] = '备注不能为空';
		}

		// 添加数据
		if ($schemeModel->add($data)) {
			$result['msg'][] = '添加成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     导出方案
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function schemeExportAction()
	{
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$version     = isset($_POST['version']) ? $_POST['version'] : 0;
		$exportFlag  = isset($_POST['exportFlag']) ? $_POST['exportFlag'] : 0;
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId);
		$result      = array('error' => false, 'msg' => array());

		if (empty($scheme)) {
			$result['error'] = true;
			$result['msg'][] = '方案选择有误';
		}

		$ruleData = json_decode($scheme['rule_json'], true);
		if (!isset($ruleData['categoryRule']) || !isset($ruleData['productRule']) || !isset($ruleData['priceRule']) || !isset($ruleData['optionRule'])) {
			$result['error'] = true;
			$result['msg'][] = '请先设定规则';
		}

		// 获取所有子规则
		$ruleList = json_decode($scheme['sub_rule_json'], true);
		if (!empty($ruleList)) {
			foreach ($ruleList as $temp) {
				if (!isset($temp['categoryRule']) || !isset($temp['productRule'])) {
					$result['error'] = true;
					$result['msg'][] = '请先设定子规则';
					break;
				}
			}
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}
		$ruleList['0'] = $ruleData;

		// 超时
		set_time_limit(0);

		// 设置导出文件的路径
		$dirName = 'Scheme' . time();
		Titan::mkdir(VAR_PATH . "download/{$dirName}/");

		// 生成分类表
		$tempArr          = array('分类名称', '分类图片', '父分类', '排序', '状态', 'meta标题', 'meta关键字', 'meta描述', 'url', '分类描述');
		$categoryFileName = 'category.csv';
		$categoryFile     = fopen(VAR_PATH . "download/{$dirName}/{$categoryFileName}", 'a');
		fputcsv($categoryFile, $tempArr);

		// 获取分组下的所有分类
		$option = array(
			'where' => array('group_id' => array('eq', $scheme['category_group_id'])),
			'order' => array('category_id' => 'ASC', 'sort' => 'ASC'),
			'col'   => 'category_id, image, parent_id, name, path, version, status, sort, description'
		);
		$categoryModel = $this->_loadModel('category');
		$categoryList  = $categoryModel->getPairs2($option);

		// 重组分类名和分类路径并导出分类
		foreach ($categoryList as $key => $val) {
			$pathArr = explode('/', $val['path']);

			// 分配规则
			if (isset($ruleList[$val['path']])) {
				$categoryList[$key]['ruleKey'] = $val['path'];
			} else {
				$categoryList[$key]['ruleKey'] = '0';
				$tempArr = $pathArr;
				array_pop($tempArr);
				while (!empty($tempArr)) {
					$tempKey = implode('/', $tempArr);
					if (isset($ruleList[$tempKey])) {
						$categoryList[$key]['ruleKey'] = $tempKey;
						break;
					}
					array_pop($tempArr);
				}
			}
			$categoryRule = $ruleList[$categoryList[$key]['ruleKey']]['categoryRule'];

			// 根据规则替换分类名称
			$categoryList[$key]['name'] = str_replace('[分类名称]', $val['name'], $categoryRule['name']);
			$pathNameArr = array();
			foreach ($pathArr as $v) {
				$pathNameArr[] = $categoryList[$v]['name'];
			}
			$categoryList[$key]['pathName'] = implode('/', $pathNameArr);

			// 插入表格
			$val['description'] = str_replace('[分类名称]', $categoryList[$key]['name'], $categoryRule['description']);
			$val['description'] = str_replace('[分类描述]', $categoryList[$key]['description'], $val['description']);
			$tempArr = array($categoryList[$key]['name'], $val['image'], $val['parent_id'] == 0 ? '' : $categoryList[$val['parent_id']]['pathName'], $val['sort'], $val['status'],
				str_replace('[分类名称]', $categoryList[$key]['name'], $categoryRule['meta_title']),
				str_replace('[分类名称]', $categoryList[$key]['name'], $categoryRule['meta_keyword']),
				str_replace('[分类名称]', $categoryList[$key]['name'], $categoryRule['meta_description']),
				str_replace(' ', '-', strtolower(str_replace('[分类名称]', $categoryList[$key]['name'], $categoryRule['url']))),
				$val['description']
			);
			fputcsv($categoryFile, $tempArr);
		}
		fclose($categoryFile);

		// 生成选项表
		$tempArr        = array('选项名称', '类型', '排序', '选项值');
		$optionFileName = 'option.csv';
		$optionFile     = fopen(VAR_PATH . "download/{$dirName}/{$optionFileName}", 'a');
		fputcsv($optionFile, $tempArr);

		// 获取附加数据
		$appendData = $this->_getProductAttributeListAndOptionList($scheme['category_group_id']);
		$optionList = $appendData['options'];
		if (!empty($optionList)) {
			foreach ($optionList as $key => $val) {
				if (isset($ruleData['optionRule'][$val['option_id']])) {
					$val['name'] = $ruleData['optionRule'][$val['option_id']];
				}
				$tempArr = array($val['name'], $val['type'], $val['sort'], empty($val['values']) ? '' : decode_json(json_encode($val['values'])));
				fputcsv($optionFile, $tempArr);
			}
		}
		fclose($optionFile);

		// 生成产品表
		$tempArr = array('产品型号', '分类名称', '产品名称', '产品图片', '价格', '特价', '选项', '排序', '状态', 'meta标题', 'meta关键字', 'meta描述', 'url', '产品描述', '短描述');
		foreach ($appendData['attributes'] as $val) {
			$tempArr[] = $val;
		}
		$productFileName = 'product.csv';
		$productFile     = fopen(VAR_PATH . "download/{$dirName}/{$productFileName}", 'a');
		fputcsv($productFile, $tempArr);

		// 获取分类分组的产品
		$productModel     = $this->_loadModel('product');
		$toAttributeModel = $this->_loadModel('product/to/attribute');
		$toPriceModel     = $this->_loadModel('product/to/price');
		$option           = array(
			'where' => array(
				'group_id'  => array('eq', $scheme['category_group_id']),
				'is_master' => array('eq', '1')
			),
			'order' => array('category_id' => 'ASC', 'sort' => 'ASC'),
			'col'   => 'product_id, category_id, sort'
		);

		// 版本筛选
		$option['where']['version'] = array($exportFlag == 1 ? 'eq' : 'elt', $version);

		$toProductModel = $this->_loadModel('category/to/product');
		$total          = $toProductModel->getTotalList($option);
		$row            = 1000;
		$maxPage        = ceil($total / $row);
		for ($page = 1; $page <= $maxPage; $page++) {
			$toProductList = $toProductModel->getList($page, $row, $option);
			$productIds    = array();
			foreach ($toProductList as $val) {
				$productIds[] = $val['product_id'];
			}

			// 获取产品属性值列表
			$tempOption = array(
				'where' => array('product_id' => array('in', $productIds)),
				'col'   => 'product_id, attribute_id, content'
			);
			$tempList        = $toAttributeModel->getAllList($tempOption);
			$toAttributeList = array();
			if (!empty($tempList)) {
				foreach ($tempList as $val) {
					$toAttributeList[$val['product_id']][$val['attribute_id']] = $val['content'];
				}
			}
			unset($tempList);

			// 获取产品价格列表
			$scheme['price_id']             = $ruleData['priceRule']['price_id'];
			$scheme['price']                = $ruleData['priceRule']['price'];
			$scheme['price_prefix']         = $ruleData['priceRule']['price_prefix'];
			$scheme['special_price']        = $ruleData['priceRule']['special_price'];
			$scheme['special_price_prefix'] = $ruleData['priceRule']['special_price_prefix'];
			$tempOption = array(
				'where' => array(
					'product_id' => array('in', $productIds),
					'price_id'   => array('eq', $scheme['price_id'])
				),
				'col'   => 'product_id,price, special_price'
			);
			$toPriceList = $toPriceModel->getPairs2($tempOption);

			// 获取产品
			$tempOption = array(
				'where' => array('product_id' => array('in', $productIds)),
				'col'   => 'product_id, sku, image, status, description, description_short'
			);
			$productList = $productModel->getPairs2($tempOption);

			// 获取产品选项值
			$toOptionValueList = $this->_getProductOptionValueByProductIds($productIds, $ruleData['optionRule']);
			foreach ($toProductList as $val) {
				if (isset($productList[$val['product_id']])) {
					$productRule = $ruleList[$categoryList[$val['category_id']]['ruleKey']]['productRule'];

					// 获取产品的属性列表
					$attributeList = array();
					foreach ($appendData['attributes'] as $k => $v) {
						if (isset($toAttributeList[$val['product_id']][$k])) {
							$attributeList['[' . $v . ']'] = $toAttributeList[$val['product_id']][$k];
						} else {
							$attributeList['[' . $v . ']'] = '';
						}
					}

					// 替换列表
					$replaceList               = $attributeList;
					$replaceList['[分类名称]'] = $categoryList[$val['category_id']]['name'];
					$replaceList['[原价]']     = isset($toPriceList[$val['product_id']]['price']) ? $toPriceList[$val['product_id']]['price'] : '0.00';
					$replaceList['[特价]']     = isset($toPriceList[$val['product_id']]['special_price']) ? $toPriceList[$val['product_id']]['special_price'] : '0.00';

					// 替换产品名称
					$val['name'] = $productRule['name'];
					foreach ($replaceList as $replaceKey => $replaceVal) {
						$val['name'] = str_replace($replaceKey, $replaceVal, $val['name']);
					}

					// 前后去空格
					$val['name'] = trim($val['name']);

					// 去除多余空格
					while (strstr($val['name'], '  ')) {
						$val['name'] = str_replace('  ', ' ', $val['name']);
					}

					// 将产品名称加入替换列表
					$replaceList['[产品名称]'] = $val['name'];

					// 替换产品的meta内容,短描述和描述
					$val['meta_title']       = $productRule['meta_title'];
					$val['meta_keyword']     = $productRule['meta_keyword'];
					$val['meta_description'] = $productRule['meta_description'];
					$descriptionShort        = $productRule['short_description'];
					$description             = $productRule['description'];
					foreach ($replaceList as $replaceKey => $replaceVal) {
						$val['meta_title']       = str_replace($replaceKey, $replaceVal, $val['meta_title']);
						$val['meta_keyword']     = str_replace($replaceKey, $replaceVal, $val['meta_keyword']);
						$val['meta_description'] = str_replace($replaceKey, $replaceVal, $val['meta_description']);
						$descriptionShort        = str_replace($replaceKey, $replaceVal, $descriptionShort);
						$description             = str_replace($replaceKey, $replaceVal, $description);
					}
					$descriptionShort = str_replace('[产品短描述]', $productList[$val['product_id']]['description_short'], $descriptionShort);
					$description      = str_replace('[产品描述]', $productList[$val['product_id']]['description'], $description);

					// 价格
					$price        = $toPriceList[$val['product_id']]['price'];
					$specialPrice = $toPriceList[$val['product_id']]['special_price'];
					eval('$price ' . $scheme['price_prefix'] . '=' . $scheme['price'] . ';');
					eval('$specialPrice ' . $scheme['special_price_prefix'] . '=' . $scheme['special_price'] . ';');
					$price        = $price > 0 ? $price : 0.00;
					$specialPrice = $specialPrice > 0 ? $specialPrice : 0.00;

					// 选项值
					$toOptionValue = isset($toOptionValueList[$val['product_id']]) ? $toOptionValueList[$val['product_id']] : array();
					$toOptionValue = json_encode($toOptionValue);
					$toOptionValue = decode_json($toOptionValue);
					$tempArr       = array(
						$productList[$val['product_id']]['sku'],
						$categoryList[$val['category_id']]['pathName'],
						$val['name'],
						$productList[$val['product_id']]['image'],
						number_format($price, 2, '.', ''),
						number_format($specialPrice, 2, '.', ''),
						$toOptionValue,
						$val['sort'],
						$productList[$val['product_id']]['status'],
						$val['meta_title'],
						$val['meta_keyword'],
						$val['meta_description'],
						str_replace(' ', '-', strtolower(str_replace('[产品名称]', $val['name'], $productRule['url']))),
						$description,
						$descriptionShort
					);
					if (!empty($attributeList)) {
						foreach ($attributeList as $v) {
							$tempArr[] = $v;
						}
					}
					fputcsv($productFile, $tempArr);
				}
			}
		}
		fclose($productFile);

		// 生成副分类表
		$tempArr             = array('产品型号', '分类名称');
		$subCategoryFileName = 'subCategory.csv';
		$subCategoryFile     = fopen(VAR_PATH . "download/{$dirName}/{$subCategoryFileName}", 'a');
		fputcsv($subCategoryFile, $tempArr);

		// 获取分类分组的产品
		$productModel = $this->_loadModel('product');
		$option       = array(
			'where' => array(
				'group_id'  => array('eq', $scheme['category_group_id']),
				'is_master' => array('eq', '0')
			),
			'order' => array('category_id' => 'ASC', 'sort' => 'ASC'),
			'col'   => 'product_id, category_id, sort'
		);

		$total   = $toProductModel->getTotalList($option);
		$row     = 1000;
		$maxPage = ceil($total / $row);
		for ($page = 1; $page <= $maxPage; $page++) {
			$toProductList = $toProductModel->getList($page, $row, $option);
			if (empty($toProductList)) {
				continue;
			}
			$productIds = array();
			foreach ($toProductList as $val) {
				$productIds[] = $val['product_id'];
			}

			// 获取产品
			$where       = array('product_id' => array('in', $productIds));
			$productList = $productModel->getPairs2(array('where' => $where, 'col' => 'product_id, sku, image, status, description, description_short'));

			foreach ($toProductList as $val) {
				if (isset($productList[$val['product_id']])) {
					$tempArr = array($productList[$val['product_id']]['sku'], $categoryList[$val['category_id']]['pathName']);
					fputcsv($subCategoryFile, $tempArr);
				}
			}
		}
		fclose($subCategoryFile);

		// 压缩CSV文件
		$zip = new ZipArchive;
		if($zip->open(VAR_PATH . "download/{$dirName}.zip", ZipArchive::OVERWRITE)===TRUE){
			// 第一个变量:路径+文件名,第二个变量:文件名
			// 若无第二个变量,将压缩整个路径的文件夹
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$categoryFileName}", $categoryFileName);
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$optionFileName}", $optionFileName);
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$productFileName}", $productFileName);
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$subCategoryFileName}", $subCategoryFileName);
			$zip->close();
		}
		@unlink(VAR_PATH . "download/{$dirName}/{$categoryFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$optionFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$productFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$subCategoryFileName}");
		@rmdir(VAR_PATH . "download/{$dirName}");
		$result['url'] = APP_HTTP . 'Var/download/' . $dirName . '.zip';
		if (!$result['error']) {
			$result['msg'][] = '导出成功';
		}
		$this->_ajaxReturn($result);
	}

	public function previewAction()
	{
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$categoryId  = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId);

		// 获取主规则
		$ruleData = json_decode($scheme['rule_json'], true);
		if (!isset($ruleData['categoryRule']) || !isset($ruleData['productRule']) || !isset($ruleData['priceRule']) || !isset($ruleData['optionRule'])) {
			$result['error'] = true;
			$result['msg'][] = '请先设定规则';
		}

		// 获取所有子规则
		$ruleList = json_decode($scheme['sub_rule_json'], true);
		if (!empty($ruleList)) {
			foreach ($ruleList as $temp) {
				if (!isset($temp['categoryRule']) || !isset($temp['productRule'])) {
					$result['error'] = true;
					$result['msg'][] = '请先设定子规则';
					break;
				}
			}
		}
		$ruleList['0'] = $ruleData;

		// 获取分组下的所有分类
		$option = array(
			'where' => array('group_id' => array('eq', $scheme['category_group_id'])),
			'order' => array('level' => 'ASC', 'sort' => 'ASC'),
			'col'   => 'category_id, parent_id, name, path'
		);
		$categoryModel = $this->_loadModel('category');
		$groupModel    = $this->_loadModel('category/group');
		$group         = $groupModel->get($scheme['category_group_id']);
		$categoryList  = $categoryModel->getPairs2($option);
		$all           = array();
		$category      = array(
			'category_id' => '0',
			'name'        => '',
		);
		if (!empty($categoryList)) {
			foreach ($categoryList as $key => $val) {
				$pathArr = explode('/', $val['path']);

				// 分配规则
				if (isset($ruleList[$val['path']])) {
					$categoryList[$key]['ruleKey'] = $val['path'];
				} else {
					$categoryList[$key]['ruleKey'] = '0';
					$tempArr = $pathArr;
					array_pop($tempArr);
					while (!empty($tempArr)) {
						$tempKey = implode('/', $tempArr);
						if (isset($ruleList[$tempKey])) {
							$categoryList[$key]['ruleKey'] = $tempKey;
							break;
						}
						array_pop($tempArr);
					}
				}
				$categoryRule = $ruleList[$categoryList[$key]['ruleKey']]['categoryRule'];

				// 根据规则替换分类名称
				$categoryList[$key]['name'] = str_replace('[分类名称]', $val['name'], $categoryRule['name']);
			}
			if (isset($categoryList[$categoryId])) {
				$category = $categoryList[$categoryId];
			}

			// 构建类目树
			foreach ($categoryList as $val) {
				$this->_data[$val['parent_id']][] = $val;
			}
			$all[] = array(
				'id'       => 0,
				'text'     => '全部',
				'children' => $this->_buildBranch(0)
			);
		}

		$this->_view->assign('schemeId', $schemeId);
		$this->_view->assign('group', $group);
		$this->_view->assign('category', $category);
		$this->_view->assign('data', $all);
		$this->_view->render('product/scheme/preview');
	}

	public function getCategoryToProductAction()
	{
		$page        = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows        = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$categoryId  = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId);

		// 获取主规则
		$ruleData = json_decode($scheme['rule_json'], true);

		// 获取所有子规则
		$ruleList = json_decode($scheme['sub_rule_json'], true);
		if (!empty($ruleList)) {
			foreach ($ruleList as $temp) {
				if (!isset($temp['categoryRule']) || !isset($temp['productRule'])) {
					$result['error'] = true;
					$result['msg'][] = '请先设定子规则';
					break;
				}
			}
		}

		// 加载模型
		$categoryModel      = $this->_loadModel('category');
		$categoryGroupModel = $this->_loadModel('category/group');
		$productModel       = $this->_loadModel('product');
		$toAttributeModel   = $this->_loadModel('product/to/attribute');
		$toPriceModel       = $this->_loadModel('product/to/price');

		$option = array(
			'where' => array('group_id' => array('eq', $scheme['category_group_id'])),
			'order' => array('product_id' => 'DESC')
		);
		if (!empty($categoryId)){
			$category = $categoryModel->get($categoryId, 'path');
			$where    = array(
				'category_id|path' => array(
					array('eq', $categoryId),
					array('like', $category['path'] . '/', 'right')
				)
			);
			$categoryIds = $categoryModel->getCol(array('where' => $where));
			$option['where']['category_id'] = array('in', !empty($categoryIds) ? $categoryIds : array(0));
		}

		// 过滤
		$where = array();
		if (isset($_POST['filter']) && count($_POST['filter']) > 0) {
			// 获取分类的产品分组
			$categoryGroup = $categoryGroupModel->get($scheme['category_group_id'], 'product_group_id');
			if (empty($categoryGroup)) {
				die('非法访问');
			}
			$where = array('group_id' => array('eq', $categoryGroup['product_group_id']));
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'status':
						if (strlen($val)) {
							$where[$key] = array('eq', $val);
						}
					break;
					case 'is_master':
						if (strlen($val)) {
							$option['where'][$key] = array('eq', $val);
						}
					break;
				}
			}
		}
		if (!empty($where)) {
			$productModel = $this->_loadModel('product');
			$productIds   = $productModel->getCol(array('where' => $where));
			$option['where']['product_id'] = array('in', !empty($productIds) ? $productIds : array(0));
		}

		$toProductModel = $this->_loadModel('category/to/product');
		$total          = $toProductModel->getTotalList($option);
		$product        = array();
		if ($total) {
			$productIds    = array();
			$toProductList = $toProductModel->getList($page, $rows, $option);
			foreach ($toProductList as $val) {
				$productIds[] = $val['product_id'];
			}

			// 获取分类列表
			$option = array('where' => array('group_id' => array('eq', $scheme['category_group_id'])));
			if (!empty($categoryId)) {
				$option['where']['category_id'] = array('in', isset($categoryIds) ? $categoryIds : array($categoryId));
			}
			$categoryList = $categoryModel->getPairs2($option);

			foreach ($categoryList as $key => $val) {
				// 默认主规则
				$categoryList[$key]['categoryRule'] = isset($ruleData['categoryRule']) ? $ruleData['categoryRule'] : '';
				$categoryList[$key]['productRule']  = isset($ruleData['productRule']) ? $ruleData['productRule'] : '';

				// 上级子规则覆盖
				$path = $val['path'];
				if (isset($ruleList[$path])) {
					$categoryList[$key]['categoryRule'] = $ruleList[$path]['categoryRule'];
					$categoryList[$key]['productRule']  = $ruleList[$path]['productRule'];
				} else {
					$tempPath = explode('/', $path);
					array_pop($tempPath);
					while (!empty($tempPath)) {
						$path = implode('/', $tempPath);
						if (isset($ruleList[$path]['categoryRule']) && isset($ruleList[$path]['productRule'])) {
							$categoryList[$key]['categoryRule'] = $ruleList[$path]['categoryRule'];
							$categoryList[$key]['productRule']  = $ruleList[$path]['productRule'];
							break;
						}
						array_pop($tempPath);
					}
				}
				$val['name'] = str_replace('[分类名称]', $val['name'], $categoryList[$key]['categoryRule']['name']);
				$categoryList[$key]['name'] = $val['name'];
			}

			// 获取分类路径
			if (!empty($categoryList)) {
				$categoryPath = array();
				foreach ($categoryList as $key => $val) {
					// 将路径分解成一个分类ID的数组
					$tempIds = explode('/', $categoryList[$key]['path']);
					$temp    = array();

					// 通过分类ID取得分类名
					foreach ($tempIds as $v) {
						if (isset($categoryList[$v])) {
							$temp[$v] = $categoryList[$v]['name'];
						}
					}
					ksort($temp);
					$temp = implode('/', $temp);
					$categoryPath[$val['category_id']] = $temp;
				}
			}

			// 获取附加数据
			$tempAttributeList = $this->_getProductAttributeList($scheme['category_group_id']);

			// 获取产品属性值列表
			$option = array(
				'where' => array('product_id' => array('in', $productIds)),
				'col'   => 'product_id, attribute_id, content'
			);
			$toAttributeList     = $toAttributeModel->getAllList($option);
			$tempToAttributeList = array();
			if (!empty($toAttributeList)) {
				foreach ($toAttributeList as $val) {
					$tempToAttributeList[$val['product_id']][$val['attribute_id']] = $val['content'];
				}
			}

			// 获取产品价格列表
			$option = array(
				'where' => array(
					'product_id' => array('in', $productIds),
					'price_id'   => array('eq', $ruleData['priceRule']['price_id'])
				),
				'col'   => 'product_id, price, special_price'
			);
			$toPriceList = $toPriceModel->getPairs2($option);

			// 获取产品选项值
			$toOptionValueList = $this->_getProductOptionValueByProductIds($productIds, $ruleData['optionRule']);

			// 获取产品列表
			$option = array(
				'where' => array('product_id' => array('in', $productIds)),
				'col'   => 'product_id, sku, image, status'
			);
			$productList = $productModel->getPairs2($option);

			// 格式化产品数据
			foreach ($toProductList as $key => $val) {
				if (isset($productList[$val['product_id']]) && isset($categoryList[$val['category_id']])) {
					$productId    = $val['product_id'];
					$categoryData = $categoryList[$val['category_id']];
					$productRule  = $categoryData['productRule'];

					// 获取产品的属性列表
					$attributeList = array();
					$attributes    = array();
					if (isset($tempToAttributeList[$productId])) {
						foreach ($tempToAttributeList[$productId] as $attribute => $value) {
							$attributeList['[' . $tempAttributeList[$attribute] . ']'] = $value;
							$attributes[] = sprintf('%s:%s', $tempAttributeList[$attribute], $value);
						}
					}

					// 显示价格
					$price        = $toPriceList[$val['product_id']]['price'];
					$specialPrice = $toPriceList[$val['product_id']]['special_price'];
					eval('$price ' . $ruleData['priceRule']['price_prefix'] . '=' . $ruleData['priceRule']['price'] . ';');
					eval('$specialPrice ' . $ruleData['priceRule']['special_price_prefix'] . '=' . $ruleData['priceRule']['special_price'] . ';');
					$price        = $price > 0 ? $price : 0.00;
					$specialPrice = $specialPrice > 0 ? $specialPrice : 0.00;
					$prices       = sprintf('原价:%s,特价:%s', $price, $specialPrice);

					// 显示选项
					$options = array();
					if (isset($toOptionValueList[$val['product_id']])) {
						$tempOptions = $toOptionValueList[$val['product_id']];
						$options     = array();
						foreach ($tempOptions as $optionName => $option) {
							$tempOptionValueList = array();
							if (!empty($option['values'])) {
								foreach ($option['values'] as $k => $v) {
									if ($option['type'] == '1') {
										$tempOptionValueList[] = sprintf('%s:%s%s', $k, $v['price_prefix'], $v['price']);
									} else {
										$tempOptionValueList[] = sprintf('%s', $v);
									}
								}
								$tempOptionValueList = implode(',', $tempOptionValueList);
								$option['required']  = $option['required'] ? '必填' : '非必填';
								$tempString          = ($option['type'] == '0') ? '%s(%s):<br/>%s' : '%s(%s):<br/>%s';
								$options[]           = sprintf($tempString, $optionName, $option['required'], $tempOptionValueList);
							}
						}
						$options = implode('<br />', $options);
					}

					// 替换列表
					$replaceList               = $attributeList;
					$replaceList['[分类名称]'] = $categoryData['name'];
					$replaceList['[原价]']     = isset($toPriceList[$productId]['price']) ? $toPriceList[$productId]['price'] : '0.00';
					$replaceList['[特价]']     = isset($toPriceList[$productId]['special_price']) ? $toPriceList[$productId]['special_price'] : '0.00';

					// 替换产品名称
					$val['name'] = $productRule['name'];
					foreach ($replaceList as $replaceKey => $replaceVal) {
						$val['name'] = str_replace($replaceKey, $replaceVal, $val['name']);
					}

					// 前后去空格
					$val['name'] = trim($val['name']);

					// 去除多余空格
					while (strstr($val['name'], '  ')) {
						$val['name'] = str_replace('  ', ' ', $val['name']);
					}

					$product[] = array(
						'product_id' => $productId,
						'image'      => $productList[$productId]['image'],
						'sku'        => $productList[$productId]['sku'],
						'name'       => $val['name'],
						'category'   => $categoryPath[$val['category_id']],
						'attributes' => implode('<br />', $attributes),
						'options'    => $options,
						'prices'     => $prices,
						'status'     => $productList[$productId]['status'],
						'is_master'  => $val['is_master'],
						'sort'       => $val['sort'],
						'by_added'   => $val['by_added'],
						'date_added' => $val['date_added']
					);
				}
			}
		}
		$this->_ajaxReturn(array('total' => $total, 'rows' => $product));
	}

	public function delDataExportAction()
	{
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId);
		$result      = array('error' => false, 'msg' => array());

		if (empty($scheme)) {
			$result['error'] = true;
			$result['msg'][] = '方案选择有误';
			$this->_ajaxReturn($result);
		}

		// 获取分类分组下的产品分组ID
		$categoryGroupModel = $this->_loadModel('category/group');
		$productGroupId     = $categoryGroupModel->get($scheme['category_group_id'], 'product_group_id');

		// 判断产品分组是否已被删除
		if (empty($productGroupId)) {
			$result['error'] = true;
			$result['msg'][] = '分类分组所属的产品分组不存在';
			$this->_ajaxReturn($result);
		}

		// 超时
		set_time_limit(0);

		// 获取产品分组下的所有产品
		$option = array(
			'where' => array(
				'group_id' => array('eq', $productGroupId['product_group_id']),
				'status'   => array('neq', '1')
			),
			'order' => array('product_id' => 'ASC'),
			'col'   => 'sku, status'
		);
		$productModel = $this->_loadModel('product');
		$productList  = $productModel->getPairs($option);

		$productFileName = 'product' . time() . '.csv';
		$productFile     = fopen(VAR_PATH . "download/{$productFileName}", 'a');
		fputcsv($productFile, array('产品型号', '状态'));
		if (!empty($productList)) {
			foreach ($productList as $key => $val) {
				fputcsv($productFile, array($key, $val));
			}
		}
		fclose($productFile);
		$result['url']   = APP_HTTP . "Var/download/{$productFileName}";
		$result['msg'][] = '导出成功';

		$this->_ajaxReturn($result);
	}

	public function exportEditionGetAction()
	{
		$schemeId = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : '0';
		if ($schemeId < 1) {
			return array();
		}
		$schemeModel     = $this->_loadModel('scheme');
		$scheme          = $schemeModel->get($schemeId, 'category_group_id');
		$categoryGroupId = isset($scheme['category_group_id']) ? $scheme['category_group_id'] : '0';
		if ($categoryGroupId < 1) {
			return array();
		}

		$categoryToProductModel = $this->_loadModel('category/to/product');
		$option = array(
			'where' => array('group_id' => array('eq', $categoryGroupId)),
			'order' => array('version' => 'ASC'),
			'col'   => 'version',
			'group' => 'version'
		);
		$data = $categoryToProductModel->getCol($option);
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe     获取主规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function ruleGetAction()
	{
		$schemeId = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		if (empty($schemeId) || !is_numeric($schemeId)) {
			$this->_ajaxReturn(array());
		}
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'category_group_id, rule_json');
		if (empty($scheme)) {
			$this->_ajaxReturn(array());
		}
		$ruleData = json_decode($scheme['rule_json'], true);

		// 解析规则数据
		$data = array();
		if (!empty($ruleData)) {
			foreach ($ruleData as $key => $val) {
				foreach ($val as $k => $v) {
					if ($key == 'optionRule') {
						$data[$key . '[' . $k . ']'] = $v;
					} else {
						$data[$key . '_' . $k] = $v;
					}
				}
			}
		}

		// 获取产品分组的详细数据(选项,价格,属性)
		$productDetailsList = $this->_getProductDetailsBySchemeId($schemeId);

		// 产品属性
		$attributesList = isset($productDetailsList['attributesList']) ? $productDetailsList['attributesList'] : array();
		if (!empty($attributesList)) {
			foreach ($attributesList as $key => $val) {
				$attributesList[$key] = "[$val]";
			}
			$data['product_attribute'] = implode(' ', $attributesList);
		}

		$this->_view->assign('optionList', $productDetailsList['optionList']);
		$this->_view->assign('priceList', $productDetailsList['priceList']);
		$this->_view->assign('ruleData', $data);
		$this->_view->render('product/scheme/rule');
	}

	/**
	 * Describe     更新主规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function ruleUpdateAction()
	{
		// 加载数据
		$schemeId = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$result   = array('error' => false, 'msg' => array());

		// 分类数据
		$categoryName            = isset($_POST['categoryRule_name']) ? trim($_POST['categoryRule_name']) : '';
		$categoryDescription     = isset($_POST['categoryRule_description']) ? trim($_POST['categoryRule_description']) : '';
		$categoryMetaTitle       = isset($_POST['categoryRule_meta_title']) ? trim($_POST['categoryRule_meta_title']) : '';
		$categoryMetaKeyword     = isset($_POST['categoryRule_meta_keyword']) ? trim($_POST['categoryRule_meta_keyword']) : '';
		$categoryMetaDescription = isset($_POST['categoryRule_meta_description']) ? trim($_POST['categoryRule_meta_description']) : '';
		$categoryUrl             = isset($_POST['categoryRule_url']) ? trim($_POST['categoryRule_url']) : '';

		// 产品数据
		$productName             = isset($_POST['productRule_name']) ? trim($_POST['productRule_name']) : '';
		$productShortDescription = isset($_POST['productRule_short_description']) ? trim($_POST['productRule_short_description']) : '';
		$productDescription      = isset($_POST['productRule_description']) ? trim($_POST['productRule_description']) : '';
		$productMetaTitle        = isset($_POST['productRule_meta_title']) ? trim($_POST['productRule_meta_title']) : '';
		$productMetaKeyword      = isset($_POST['productRule_meta_keyword']) ? trim($_POST['productRule_meta_keyword']) : '';
		$productMetaDescription  = isset($_POST['productRule_meta_description']) ? trim($_POST['productRule_meta_description']) : '';
		$productUrl              = isset($_POST['productRule_url']) ? trim($_POST['productRule_url']) : '';

		// 获取产品分组的详细数据(选项,价格,属性)
		$productDetailsList = $this->_getProductDetailsBySchemeId($schemeId);
		$optionList         = $productDetailsList['optionList'];

		// 选项数据
		if (empty($optionList)) {
			$result['error'] = true;
			$result['msg'][] = '选项规则更新失败';
			$this->_ajaxReturn($result);
		}
		foreach ($optionList as $val) {
			$optionRule[$val['option_id']] = (isset($_POST['optionRule'][$val['option_id']]) && !empty($_POST['optionRule'][$val['option_id']])) ? $_POST['optionRule'][$val['option_id']] : $val['name'];
		}

		// 价格数据
		$priceId            = isset($_POST['priceRule_price_id']) ? $_POST['priceRule_price_id'] : 0;
		$pricePrefix        = isset($_POST['priceRule_price_prefix']) ? $_POST['priceRule_price_prefix'] : '';
		$price              = isset($_POST['priceRule_price']) ? $_POST['priceRule_price'] : '';
		$specialPricePrefix = isset($_POST['priceRule_special_price_prefix']) ? $_POST['priceRule_special_price_prefix'] : '';
		$specialPrice       = isset($_POST['priceRule_special_price']) ? $_POST['priceRule_special_price'] : '';

		// 验证数据
		if (empty($schemeId) || !is_numeric($schemeId)) {
			$result['error'] = true;
			$result['msg'][] = '主规则更新失败';
			$this->_ajaxReturn($result);
		}

		// 验证分类数据
		if (empty($categoryName)) {
			$result['error'] = true;
			$result['msg'][] = '分类名称不能为空';
		} elseif (false === strpos($categoryName, '[分类名称]')) {
			$result['error'] = true;
			$result['msg'][] = '分类名称必须包含[分类名称]的替换字符';
		}
		if (empty($categoryMetaTitle)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta标题不能为空';
		}
		if (empty($categoryMetaKeyword)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta关键词不能为空';
		}
		if (empty($categoryMetaDescription)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta描述不能为空';
		}

		// 验证产品数据
		if (empty($productName)) {
			$result['error'] = true;
			$result['msg'][] = '产品名称不能为空';
		} elseif (!(false === strpos($productName, '[产品名称]'))) {
			$result['error'] = true;
			$result['msg'][] = '产品名称不能包含[产品名称]的替换字符';
		}
		if (empty($productMetaTitle)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta标题不能为空';
		}
		if (empty($productMetaKeyword)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta关键词不能为空';
		}
		if (empty($productMetaDescription)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta描述不能为空';
		}

		// 验证价格数据
		$prefixList      = array('+', '-', '*');
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$where = array(
			'dictionary_id' => array('eq', $priceId),
			'type'           => array('eq', '产品价格')
		);
		if (empty($priceId)) {
			$result['error'] = true;
			$result['msg'][] = '产品价格选择不能为空';
		} elseif (!$dictionaryModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '产品价格选择有误';
		}
		if (empty($pricePrefix) || !in_array($pricePrefix, $prefixList)) {
			$result['error'] = true;
			$result['msg'][] = '产品价格前缀选择有误';
		}
		if (!is_numeric($price)) {
			$result['error'] = true;
			$result['msg'][] = '产品价格输入有误';
		}
		if (empty($specialPricePrefix) || !in_array($specialPricePrefix, $prefixList)) {
			$result['error'] = true;
			$result['msg'][] = '产品特价前缀选择有误';
		}
		if (!is_numeric($specialPrice)) {
			$result['error'] = true;
			$result['msg'][] = '产品特价输入有误';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'rule_json');
		if (empty($scheme)) {
			$result['error'] = true;
			$result['msg'][] = '主规则更新失败';
			$this->_ajaxReturn($result);
		}

		// 更新数据
		$priceRule = array(
			'price_id'             => $priceId,
			'price'                => $price,
			'price_prefix'         => $pricePrefix,
			'special_price'        => $specialPrice,
			'special_price_prefix' => $specialPricePrefix
		);

		$ruleData                 = json_decode($scheme['rule_json'], true);
		$ruleData['priceRule']    = $priceRule;
		$ruleData['optionRule']   = $optionRule;
		$ruleData['categoryRule'] = array(
			'name'             => $categoryName,
			'description'      => $categoryDescription,
			'meta_title'       => $categoryMetaTitle,
			'meta_keyword'     => $categoryMetaKeyword,
			'meta_description' => $categoryMetaDescription,
			'url'              => $categoryUrl
		);
		$ruleData['productRule']  = array(
			'name'              => $productName,
			'short_description' => $productShortDescription,
			'description'       => $productDescription,
			'meta_title'        => $productMetaTitle,
			'meta_keyword'      => $productMetaKeyword,
			'meta_description'  => $productMetaDescription,
			'url'               => $productUrl
		);

		$data = array(
			'rule_json'     => decode_json(json_encode($ruleData)),
			'by_modified'   => $_SESSION['user_account'],
			'date_modified' => now()
		);
		$schemeModel = $this->_loadModel('scheme');
		if ($schemeModel->update($data, $schemeId)) {
			$result['msg'][] = '主规则更新成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '主规则更新失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     子规则管理
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleAction()
	{
		$schemeId                 = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$schemeModel              = $this->_loadModel('scheme');
		$scheme                   = $schemeModel->get($schemeId, 'scheme_id, category_group_id');
		$groupModel               = $this->_loadModel('category/group');
		$categoryGroup            = $groupModel->get($scheme['scheme_id'], 'name');
		$scheme['category_group'] = $categoryGroup['name'];

		// 获取方案分类分组的子分类数据
		$option = array(
			'where' => array(
				'group_id' => array('eq', $scheme['category_group_id']),
				'status'   => array('eq', '1')
			),
			'order' => array('path' => 'ASC', 'sort' => 'ASC'),
			'col'   => 'category_id, parent_id, name'
		);
		$categoryModel = $this->_loadModel('category');
		$categoryList  = $categoryModel->getAllList($option);
		if (!empty($categoryList)) {
			foreach ($categoryList as $val) {
				$this->_data[$val['parent_id']][] = $val;
			}
		}
		$data = $this->_buildBranch(0);

		// 获取方案的属性列表
		$tempAttributeList = $this->_getProductAppendData($scheme['category_group_id'], 1);
		if (!empty($tempAttributeList)) {
			foreach ($tempAttributeList as $key => $val) {
				$tempAttributeList[$key] = "[$val]";
			}
			$attributeList = implode(' ', $tempAttributeList);
		}

		$this->_view->assign('scheme', $scheme);
		$this->_view->assign('attributeList', $attributeList);
		$this->_view->assign('data', $data);
		$this->_view->render('product/scheme/subRule');
	}

	/**
	 * Describe     子规则列表
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleListAction()
	{
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'sub_rule_json');
		if (empty($scheme)) {
			$this->_ajaxReturn(array());
		}
		$subRuleArr = json_decode($scheme['sub_rule_json'], true);
		$data       = array();
		if (!empty($subRuleArr)) {
			foreach ($subRuleArr as $key => $val) {
				$val['path'] = $key;
				$val['isset_rule'] = (isset($val['categoryRule']) && isset($val['productRule'])) ? '1' : '0';
				$data[] = $val;
			}
		}

		$this->_ajaxReturn($data);
	}

	/**
	 * Describe     添加子规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleAddAction()
	{
		// 加载数据
		$schemeId   = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$categoryId = isset($_POST['category_id']) ? $_POST['category_id'] : 0;
		$result     = array('error' => false, 'msg' => array());

		// 验证数据
		if (empty($schemeId) || !is_numeric($schemeId)) {
			$result['error'] = true;
			$result['msg'][] = '添加分类子规则失败';
			$this->_ajaxReturn($result);
		}
		$schemeModel   = $this->_loadModel('scheme');
		$categoryModel = $this->_loadModel('category');
		$scheme        = $schemeModel->get($schemeId, 'scheme_id, category_group_id, rule_json, sub_rule_json');
		$category      = $categoryModel->get($categoryId, 'name, group_id, path');
		if (empty($scheme) || empty($category)) {
			$result['error'] = true;
			$result['msg'][] = '添加分类子规则失败';
			$this->_ajaxReturn($result);
		} elseif (empty($scheme['rule_json'])) {
			$result['error'] = true;
			$result['msg'][] = '请先设定主规则';
			$this->_ajaxReturn($result);
		} elseif ($scheme['category_group_id'] != $category['group_id']) {
			$result['error'] = true;
			$result['msg'][] = '分类选择有误';
			$this->_ajaxReturn($result);
		}
		$subRuleArr = json_decode($scheme['sub_rule_json'], true);
		if (!empty($subRuleArr) && key_exists($category['path'], $subRuleArr)) {
			$result['error'] = true;
			$result['msg'][] = '该分类已存在';
			$this->_ajaxReturn($result);
		}

		// 子规则数据
		$categoryIds  = explode('/', $category['path']);
		$where        = array('category_id' => array('in', $categoryIds));
		$categoryList = $categoryModel->getPairs(array('where' => $where, 'col' => 'category_id, name'));
		foreach ($categoryIds as $id) {
			if (isset($categoryList[$id])) {
				$data[$id] = $categoryList[$id];
			} else {
				$result['error'] = true;
				$result['msg'][] = '数据错误';
				$this->_ajaxReturn($result);
			}
		}
		$subRuleArr[$category['path']] = array(
			'category_id'   => $categoryId,
			'category_name' => implode('>', $data),
			'by_added'      => $_SESSION['user_account'],
			'by_modified'   => '',
			'date_added'    => now(),
			'date_modified' => ''
		);

		// 添加默认规则
		$path     = $category['path'];
		$ruleData = json_decode($scheme['rule_json'], true);

		// 默认主规则
		$subRuleArr[$category['path']]['categoryRule'] = isset($ruleData['categoryRule']) ? $ruleData['categoryRule'] : '';
		$subRuleArr[$category['path']]['productRule']  = isset($ruleData['productRule']) ? $ruleData['productRule'] : '';

		// 上级子规则覆盖
		$tempPath = explode('/', $path);
		array_pop($tempPath);
		while (!empty($tempPath)) {
			$path = implode('/', $tempPath);
			if (isset($subRuleArr[$path]['categoryRule']) && isset($subRuleArr[$path]['productRule'])) {
				$subRuleArr[$category['path']]['categoryRule'] = $subRuleArr[$path]['categoryRule'];
				$subRuleArr[$category['path']]['productRule']  = $subRuleArr[$path]['productRule'];
				break;
			}
			array_pop($tempPath);
		}

		// 添加数据
		$data = array('sub_rule_json' => decode_json(json_encode($subRuleArr)));
		if ($schemeModel->update($data, $schemeId)) {
			$result['msg'][] = '添加分类子规则成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加分类子规则失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     获取子规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleGetAction()
	{
		$schemeId    = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$path        = isset($_GET['path']) ? $_GET['path'] : '';
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'sub_rule_json, category_group_id');
		if (empty($scheme)) {
			$this->_ajaxReturn(array());
		}
		$subRuleArr = json_decode($scheme['sub_rule_json'], true);

		// 解析规则数据
		$data = array();
		if (isset($subRuleArr[$path]) && !empty($subRuleArr[$path])) {
			if (isset($subRuleArr[$path]['categoryRule']) && isset($subRuleArr[$path]['productRule'])) {
				$tempData = array(
					'categoryRule' => $subRuleArr[$path]['categoryRule'],
					'productRule'  => $subRuleArr[$path]['productRule']
				);
				foreach ($tempData as $key => $val) {
					foreach ($val as $k => $v) {
						$data[$key . '_' . $k] = $v;
					}
				}
			}
		}

		// 获取产品属性
		$attributeList = $this->_getProductAttributeList($scheme['category_group_id']);
		if (!empty($attributeList)) {
			foreach ($attributeList as $key => $val) {
				$attributeList[$key] = "[$val]";
			}
			$data['product_attribute'] = implode(',', $attributeList);
		}

		$this->_ajaxReturn($data);
	}

	/**
	 * Describe     更新子规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleUpdateAction()
	{
		// 加载数据
		$schemeId = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : 0;
		$path     = isset($_GET['path']) ? $_GET['path'] : '';
		$result   = array('error' => false, 'msg' => array());

		// 分类数据
		$categoryName            = isset($_POST['categoryRule_name']) ? trim($_POST['categoryRule_name']) : '';
		$categoryDescription     = isset($_POST['categoryRule_description']) ? trim($_POST['categoryRule_description']) : '';
		$categoryMetaTitle       = isset($_POST['categoryRule_meta_title']) ? trim($_POST['categoryRule_meta_title']) : '';
		$categoryMetaKeyword     = isset($_POST['categoryRule_meta_keyword']) ? trim($_POST['categoryRule_meta_keyword']) : '';
		$categoryMetaDescription = isset($_POST['categoryRule_meta_description']) ? trim($_POST['categoryRule_meta_description']) : '';
		$categoryUrl             = isset($_POST['categoryRule_url']) ? trim($_POST['categoryRule_url']) : '';

		// 产品数据
		$productName             = isset($_POST['productRule_name']) ? trim($_POST['productRule_name']) : '';
		$productShortDescription = isset($_POST['productRule_short_description']) ? trim($_POST['productRule_short_description']) : '';
		$productDescription      = isset($_POST['productRule_description']) ? trim($_POST['productRule_description']) : '';
		$productMetaTitle        = isset($_POST['productRule_meta_title']) ? trim($_POST['productRule_meta_title']) : '';
		$productMetaKeyword      = isset($_POST['productRule_meta_keyword']) ? trim($_POST['productRule_meta_keyword']) : '';
		$productMetaDescription  = isset($_POST['productRule_meta_description']) ? trim($_POST['productRule_meta_description']) : '';
		$productUrl              = isset($_POST['productRule_url']) ? trim($_POST['productRule_url']) : '';

		// 验证数据
		if (empty($schemeId) || !is_numeric($schemeId)) {
			$result['error'] = true;
			$result['msg'][] = '子规则更新失败';
			$this->_ajaxReturn($result);
		}

		// 验证分类数据
		if (empty($categoryName)) {
			$result['error'] = true;
			$result['msg'][] = '分类名称不能为空';
		} elseif (false === strpos($categoryName, '[分类名称]')) {
			$result['error'] = true;
			$result['msg'][] = '分类名称必须包含[分类名称]的替换字符';
		}
		if (empty($categoryMetaTitle)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta标题不能为空';
		}
		if (empty($categoryMetaKeyword)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta关键词不能为空';
		}
		if (empty($categoryMetaDescription)) {
			$result['error'] = true;
			$result['msg'][] = '分类meta描述不能为空';
		}

		// 验证产品数据
		if (empty($productName)) {
			$result['error'] = true;
			$result['msg'][] = '产品名称不能为空';
		} elseif (!(false === strpos($productName, '[产品名称]'))) {
			$result['error'] = true;
			$result['msg'][] = '产品名称不能包含[产品名称]的替换字符';
		}
		if (empty($productMetaTitle)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta标题不能为空';
		}
		if (empty($productMetaKeyword)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta关键词不能为空';
		}
		if (empty($productMetaDescription)) {
			$result['error'] = true;
			$result['msg'][] = '产品meta描述不能为空';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'sub_rule_json');
		if (empty($scheme)) {
			$result['error'] = true;
			$result['msg'][] = '子规则更新失败';
			$this->_ajaxReturn($result);
		} elseif (empty($scheme['rule_json'])) {
			$result['error'] = true;
			$result['msg'][] = '请先设定主规则';
			$this->_ajaxReturn($result);
		}

		// 更新数据
		$subRuleArr = json_decode($scheme['sub_rule_json'], true);
		$subRuleArr[$path]['categoryRule'] = array(
			'name'             => $categoryName,
			'description'      => $categoryDescription,
			'meta_title'       => $categoryMetaTitle,
			'meta_keyword'     => $categoryMetaKeyword,
			'meta_description' => $categoryMetaDescription,
			'url'              => $categoryUrl
		);
		$subRuleArr[$path]['productRule'] = array(
			'name'              => $productName,
			'short_description' => $productShortDescription,
			'description'       => $productDescription,
			'meta_title'        => $productMetaTitle,
			'meta_keyword'      => $productMetaKeyword,
			'meta_description'  => $productMetaDescription,
			'url'               => $productUrl
		);
		$subRuleArr[$path]['by_modified']   = $_SESSION['user_account'];
		$subRuleArr[$path]['date_modified'] = now();
		$data = array('sub_rule_json' => json_encode($subRuleArr));
		if ($schemeModel->update($data, $schemeId)) {
			$result['msg'][] = '子规则更新成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '子规则更新失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     删除子规则
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 */
	public function subRuleDelAction()
	{
		$schemeId    = isset($_POST['scheme_id']) ? $_POST['scheme_id'] : 0;
		$path        = isset($_POST['path']) ? $_POST['path'] : '';
		$result      = array('error' => false, 'msg' => array());
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'sub_rule_json');
		if (empty($scheme)) {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
			$this->_ajaxReturn($result);
		}
		$subRuleArr = json_decode($scheme['sub_rule_json'], true);
		unset($subRuleArr[$path]);
		$data = array('sub_rule_json' => json_encode($subRuleArr));
		if ($schemeModel->update($data, $schemeId)) {
			$result['msg'][] = '删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     根据方案id获取选项列表和价格列表
	 * User         陈伟义
	 * DateAdded    2016-10-10
	 * DateModified
	 *
	 * @param $schemeId
	 * @return array|false
	 */
	private function _getProductDetailsBySchemeId($schemeId)
	{
		if (empty($schemeId) || !is_numeric($schemeId)) {
			return array();
		}

		// 获取分类分组id
		$schemeModel = $this->_loadModel('scheme');
		$scheme      = $schemeModel->get($schemeId, 'category_group_id');
		if (empty($scheme)) {
			return array();
		}

		// 获取分类分组数据
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroup      = $categoryGroupModel->get($scheme['category_group_id'], 'product_group_id');
		if (empty($categoryGroup)) {
			return array();
		}

		// 获取产品分组数据
		$productGroupModel = $this->_loadModel('product/group');
		$productGroup      = $productGroupModel->get($categoryGroup['product_group_id'], 'attributes, options, prices');
		if (empty($productGroup)) {
			return array();
		}

		// 获取选项列表
		$optionModel = $this->_loadModel('product/option');
		$option      = array(
			'where' => array('option_id' => array('in', $productGroup['options'])),
			'order' => array('sort' => 'ASC'),
			'col'   => 'option_id, name'
		);
		$optionList = $optionModel->getAllList($option);

		// 获取价格列表
		$option = array(
			'where' => array(
				'dictionary_id' => array('in', $productGroup['prices']),
				'type'   => array('eq', '产品价格'),
				'status' => array('eq', '1')
			),
			'order' => array('sort' => 'ASC'),
			'col'   => 'dictionary_id, name'
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		$priceList       = $dictionaryModel->getAllList($option);

		// 获取属性列表
		$attributesList = $this->_getAttributeListByAttributes($productGroup['attributes']);

		$data = array(
			'optionList'     => $optionList,
			'priceList'      => $priceList,
			'attributesList' => $attributesList
		);

		return $data;
	}

	/**
	 * Describe     获取选项的所有数据
	 * User         陈伟义
	 * DateAdded    2016-08-15
	 * DateModified
	 *
	 * @param $options
	 * @return array
	 */
	private function _getOptionListByOptions($options)
	{
		// 加载模型
		$optionModel      = $this->_loadModel('product/option');
		$optionValueModel = $this->_loadModel('product/option/value');

		$option = array(
			'where' => array(
				'option_id' => array('in', $options)
			),
			'order' => array('sort' => 'ASC'),
			'col'   => 'option_id, type, name, sort'
		);
		$optionList = $optionModel->getAllList($option);
		if (!empty($optionList)) { // 获取选项值数据
			$optionIds = array();
			foreach ($optionList as $val) {
				$optionIds[$val['option_id']] = $val['option_id'];
			}
			$option = array(
				'where' => array('option_id' => array('in', $optionIds)),
				'order' => array('sort' => 'ASC'),
				'col'   => 'option_value_id, option_id, name, sort'
			);
			$optionValueList = $optionValueModel->getAllList($option);
			if (!empty($optionValueList)) {
				foreach ($optionValueList as $val) {
					$tempOptionValueList[$val['option_id']][] = array(
						'name' => $val['name'],
						'sort' => $val['sort']
					);
				}
			}

			// 重组选项数组
			foreach ($optionList as $key => $val) {
				$optionId = $val['option_id'];
				$optionList[$key]['values'] = isset($tempOptionValueList[$optionId]) ? $tempOptionValueList[$optionId] : array();
			}
		}

		return $optionList;
	}

	/**
	 * Describe     获取属性列表
	 * User         陈伟义
	 * DateAdded    2016-08-15
	 * DateModified
	 *
	 * @param $attributes
	 * @return multitype
	 */
	private function _getAttributeListByAttributes($attributes)
	{
		$where = array(
			'dictionary_id' => array('in', $attributes)
		);
		$dictionaryModel = $this->_loadModel('system/dictionary');
		return $dictionaryModel->getPairs(array('where' => $where, 'col' => 'dictionary_id, name'));
	}

	/**
	 * Describe     获取产品附加数据
	 * User         陈伟义
	 * DateAdded    2016-08-15
	 * DateModified
	 *
	 * @param $categoryGroupId
	 * @param string $mod 获取模式(0:获取属性和选项;1:获取属性;2:获取选项;)
	 * @return array|multitype
	 */
	private function _getProductAppendData($categoryGroupId, $mod = '0')
	{
		if (empty($categoryGroupId) || !is_numeric($categoryGroupId) || !in_array($mod, array('0', '1', '2'))) {
			return array();
		}

		// 获取分类分组数据
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroup      = $categoryGroupModel->get($categoryGroupId, 'product_group_id');
		if (empty($categoryGroup)) {
			return array();
		}

		// 获取产品分组数据
		$productGroupModel = $this->_loadModel('product/group');
		$productGroup      = $productGroupModel->get($categoryGroup['product_group_id'], 'attributes, options');
		if (empty($productGroup)) {
			return array();
		}

		// 获取追加数据
		switch ($mod) {
			case '0':
				$data = array(
					'options'    => $this->_getOptionListByOptions($productGroup['options']),
					'attributes' => $this->_getAttributeListByAttributes($productGroup['attributes'])
				);
			break;
			case '1':
				$data = $this->_getAttributeListByAttributes($productGroup['attributes']);
			break;
			case '2':
				$data = $this->_getOptionListByOptions($productGroup['options']);
			break;
		}

		return $data;
	}

	/**
	 * Describe     获取分类分组的所有属性和选项
	 * User         陈伟义
	 * DateAdded    2016-08-15
	 * DateModified
	 *
	 * @param $categoryGroupId
	 * @return array|multitype
	 */
	private function _getProductAttributeListAndOptionList($categoryGroupId)
	{
		return $this->_getProductAppendData($categoryGroupId, '0');
	}

	/**
	 * Describe     获取产品分组的属性列表
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified 2016-08-15
	 *
	 * @param $categoryGroupId
	 * @return array|multitype
	 */
	private function _getProductAttributeList($categoryGroupId)
	{
		return $this->_getProductAppendData($categoryGroupId, '1');
	}

	/**
	 * Describe      通过产品id获取选项和选项值数据
	 * User          王天贵
	 * DateAdded     2016-08-16
	 * DateModified
	 *
	 * @param $productIds
	 * @param $optionRule
	 * @return array|string
	 */
	private function _getProductOptionValueByProductIds($productIds, $optionRule)
	{
		$result = array();
		if (!is_array($productIds) || empty($productIds)) {
			return array();
		}

		// 加载模型
		$productOptionModel        = $this->_loadModel('product/option');
		$productToOptionModel      = $this->_loadModel('product/to/option');
		$productOptionValueModel   = $this->_loadModel('product/option/value');
		$productToOptionValueModel = $this->_loadModel('product/to/option/value');

		// 获取产品与选项关系数据
		$where        = array('product_id' => array('in', $productIds),);
		$toOptionList = $productToOptionModel->getAllList(array('where' => $where));
		if (empty($toOptionList)) {
			return array();
		}

		// 获取所有optionIds
		$optionIds = array();
		if (!empty($toOptionList)) {
			foreach ($toOptionList as $toOption) {
				$optionIds[] = $toOption['option_id'];
			}
			$optionIds = array_unique($optionIds);
		}
		if (empty($optionIds)) {
			return array();
		}

		// 获取选项名字和类型
		if (empty($optionIds)) {
			return array();
		}
		$where      = array('option_id' => array('in', $optionIds));
		$optionList = $productOptionModel->getPairs2(array('where' => $where, 'col' => 'option_id, type, name'));

		// 选项规则替换
		if (!empty($optionList)) {
			foreach ($optionList as $key => $val) {
				if (isset($optionRule[$val['option_id']])) {
					$optionList[$key]['name'] = $optionRule[$val['option_id']];
				}
			}
		}

		// 获取产品选项值数据
		$where             = array('product_id' => array('in', $productIds),);
		$toOptionValueList = $productToOptionValueModel->getAllList(array('where' => $where));

		// 获取选项值Ids
		$optionValueIds = array();
		if (!empty($toOptionValueList)) {
			foreach ($toOptionValueList as $toOptionValue) {
				$optionValueIds[] = $toOptionValue['option_value_id'];
			}
			$optionValueIds = array_unique($optionValueIds);
		}

		// 获取选项值名字
		$where           = array('option_value_id' => array('in', $optionValueIds));
		$optionValueList = $productOptionValueModel->getPairs(array('where' => $where, 'col' => 'option_value_id, name'));

		if (!empty($toOptionList) && !empty($toOptionValueList)) {
			foreach ($toOptionList as $toOption) {
				$productId  = $toOption['product_id'];
				$optionId   = $toOption['option_id'];
				$optionName = $optionList[$optionId]['name'];
				$result[$productId][$optionName]['required'] = $toOption['required'];
				$result[$productId][$optionName]['type'] = $optionList[$optionId]['type'];
			}

			foreach ($toOptionValueList as $val){
				if (isset($result[$val['product_id']])) {
					$optionName = isset($optionList[$val['option_id']]['name']) ? $optionList[$val['option_id']]['name'] : '';
					$type       = $result[$val['product_id']][$optionName]['type'];
					if ($type) {
						$optionValueName = isset($optionValueList[$val['option_value_id']]) ? $optionValueList[$val['option_value_id']] : '';
						$result[$val['product_id']][$optionName]['values'][$optionValueName]['price']        = $val['price'];
						$result[$val['product_id']][$optionName]['values'][$optionValueName]['price_prefix'] = $val['price_prefix'];
					} else {
						$result[$val['product_id']][$optionName]['values']['price']        = $val['price'];
						$result[$val['product_id']][$optionName]['values']['price_prefix'] = $val['price_prefix'];
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Describe     创建分类节点
	 * User         陈伟义
	 * DateAdded    2016-08-12
	 * DateModified
	 *
	 * @param $parentId
	 * @return array
	 */
	private function _buildBranch($parentId)
	{
		$result = array();
		if (isset($this->_data[$parentId])) {
			foreach ($this->_data[$parentId] as $category) {
				$result[] = array(
					'id'       => $category['category_id'],
					'state'	   => isset($this->_data[$category['category_id']]) ? 'closed' : '',
					'text'     => $category['name'],
					'children' => isset($this->_data[$category['category_id']]) ? $this->_buildBranch($category['category_id']) : array()
				);
			}
		}
		return $result;
	}
}
