<?php

/**
 * Class Product_CategoryController
 * User  黄力军
 * Date  2016-8-12
 */
class Product_CategoryController extends CustomController
{
	private $_data = array();

	protected function _construct()
	{
		parent::_construct();
		$this->_validationRole('product/category/group');
	}

	public function formatAction()
	{
		$result	= array('error' => false, 'msg' => array());
		if ((!isset($_FILES['uploadallcategory-fl']['error']) || $_FILES['uploadallcategory-fl']['error'] == 4) ||
			(!isset($_FILES['categoryToProduct-fl']['error']) || $_FILES['categoryToProduct-fl']['error'] == 4) ||
			(!isset($_FILES['subCategory-fl']['error']) || $_FILES['subCategory-fl']['error'] == 4)) {
			$result['error'] = true;
			$result['msg'][] = '上传的数据不能为空';
			$this->_ajaxReturn($result);
		}
		$categoryFileLocation    = isset($_FILES['uploadallcategory-fl']['tmp_name']) ? $_FILES['uploadallcategory-fl']['tmp_name'] : '';
		$toProductFileLocation   = isset($_FILES['categoryToProduct-fl']['tmp_name']) ? $_FILES['categoryToProduct-fl']['tmp_name'] : '';
		$subCategoryFileLocation = isset($_FILES['subCategory-fl']['tmp_name']) ? $_FILES['subCategory-fl']['tmp_name'] : '';
		if (!file_exists($categoryFileLocation) || !file_exists($toProductFileLocation) || !file_exists($subCategoryFileLocation)) {
			$result['error'] = true;
			$result['msg'][] = '文件不存在。';
			$this->_ajaxReturn($result);
		} elseif (!($categoryHandle = fopen($categoryFileLocation, 'r')) ||
			!($toProductHandle = fopen($toProductFileLocation, 'r')) ||
			!($subCategoryHandle = fopen($subCategoryFileLocation, 'r'))) {
			$result['error'] = true;
			$result['msg'][] = '文件无法读取。';
			$this->_ajaxReturn($result);
		}

		// 设置导出文件的路径
		$dirName = 'category' . time();
		Titan::mkdir(VAR_PATH . "download/{$dirName}/");

		// 超时
		set_time_limit(0);

		// 丢弃第一行数据
		fgetcsv($categoryHandle);
		// 生成CSV表
		$categoryFileName = 'category.csv';
		$categoryFile     = fopen(VAR_PATH . "download/{$dirName}/{$categoryFileName}", 'a');
		fputcsv($categoryFile, array('分类名称', '图片路径', '父分类', '排序'));
		$categoryPathArr = array();
		$i = 2;
		while ($row = fgetcsv($categoryHandle)) {
			$row    = array_map('trim', $row);
			$sku    = $row[0];
			$name   = $row[1];
			$image  = $row[3];
			$sort   = ($i - 1) * 10;
			$parent = $row[5];
			$categoryPathArr[$sku] = (isset($categoryPathArr[$parent]) ? $categoryPathArr[$parent] . '/' : '') . $name;
			$parentPath            = isset($categoryPathArr[$parent]) ? $categoryPathArr[$parent] : $parent;
			fputcsv($categoryFile, array($name, $image, $parentPath, $sort));
			$i++;
		}
		fclose($categoryFile);

		// 生成主分类产品CSV表
		$categoryToProductFileName = 'categoryToProduct.csv';
		$this->_productToCategory($categoryToProductFileName, $dirName, $toProductHandle, $categoryPathArr);

		// 生成副分类产品CSV表
		$subCategoryFileName = 'subCategoryToProduct.csv';
		$this->_productToCategory($subCategoryFileName, $dirName, $subCategoryHandle, $categoryPathArr);

		// 压缩CSV文件
		$zip = new ZipArchive();
		if($zip->open(VAR_PATH . "download/{$dirName}.zip", ZipArchive::OVERWRITE)==TRUE){
			// 第一个变量:路径+文件名,第二个变量:文件名
			// 若无第二个变量,将压缩整个路径的文件夹
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$categoryFileName}", $categoryFileName);
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$categoryToProductFileName}", $categoryToProductFileName);
			$zip->addFile(VAR_PATH . "download/{$dirName}/{$subCategoryFileName}", $subCategoryFileName);
			$zip->close();
		}

		// 删除文件
		@unlink(VAR_PATH . "download/{$dirName}/{$categoryFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$categoryToProductFileName}");
		@unlink(VAR_PATH . "download/{$dirName}/{$subCategoryFileName}");
		@rmdir(VAR_PATH . "download/{$dirName}");
		$result['url'] = APP_HTTP . "Var/download/{$dirName}.zip";
		$result['msg'][] = '格式化完成';

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe     格式化产品和分类的关系
	 * User         陈伟义
	 * DateAdded    2016-10-11
	 * DateModified
	 *
	 * @param $fileName
	 * @param $dirName
	 * @param $handle
	 * @param $pathArr
	 */
	private function _productToCategory($fileName, $dirName, $handle, $pathArr)
	{
		$productFile = fopen(VAR_PATH . "download/{$dirName}/{$fileName}", 'a');
		fputcsv($productFile, array('产品型号', '分类路径', '排序'));

		// 丢弃第一行数据
		fgetcsv($handle);
		$rowNum = 2;
		while ($row = fgetcsv($handle)) {
			$row  = array_map('trim', $row);
			$path = isset($pathArr[$row[1]]) ? $pathArr[$row[1]] : '分类不存在';
			fputcsv($productFile, array($row[0], $path, ($rowNum - 1) * 10));
			$rowNum++;
		}
		fclose($productFile);
	}

	/**
	 * Describe      分类管理主页
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function indexAction()
	{
		$this->groupAction();
	}

	/**
	 * Describe      获取分组
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupListAction()
	{
		$page              = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows              = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$groupModel        = $this->_loadModel('category/group');
		$productGroupModel = $this->_loadModel('product/group');
		$product           = $productGroupModel->getPairs(array('col' => 'group_id, name'));
		$option            = array('order' => array('product_group_id' => 'ASC', 'sort' => 'ASC'));
		$total = $groupModel->getTotalList();
		$data  = array();
		if ($total) {
			$data = $groupModel->getList($page, $rows, $option);
			foreach ($data as $key => $val) {
				if (isset($product[$val['product_group_id']])) {
					$data[$key]['product_group_id'] = $product[$val['product_group_id']];
				}
			}
		}
		$this->_ajaxReturn(array('total' => $total, 'rows' => $data));
	}

	/**
	 * Describe      展示分组
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupAction()
	{
		$option = array('col' => 'group_id, name');
		$productGroupModel = $this->_loadModel('product/group');
		$productGroupList  = $productGroupModel->getAllList($option);
		$this->_view->assign('productGroupList', $productGroupList);
		$this->_view->render('product/category/group');
	}

	/**
	 * Describe      添加分组
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupAddAction()
	{
		// 加载数据
		$data = $this->_loadGroupData();
		$data['product_group_id'] = isset($_POST['product_group_id']) ? (int)$_POST['product_group_id'] : 0;
		$data['by_added']         = $_SESSION['user_account'];
		$data['date_added']       = now();

		// 验证数据
		$result            = $this->_validateGroupData($data);
		$productGroupModel = $this->_loadModel('product/group');
		if (!$productGroupModel->validate($data['product_group_id'])) {
			$result['error'] = true;
			$result['msg'][] = '产品分组不存在';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		// 添加数据
		$groupModel = $this->_loadModel('category/group');
		if ($groupModel->add($data)) {
			$result['msg'][] = '添加成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取选中分组信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupGetAction()
	{
		$groupId = isset($_GET['group_id']) ? $_GET['group_id'] : 0;
		$data    = array();
		if (empty($groupId) || !is_numeric($groupId)) {
			$this->_ajaxReturn($data);
		}
		$groupModel = $this->_loadModel('category/group');
		$data       = $groupModel->get($groupId, 'name, product_group_id, sort, remarks');
		$this->_ajaxReturn($data);
	}

	/**
	 * Describe      更新选中分组信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupUpdateAction()
	{
		// 加载数据
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		if (empty($groupId) || !is_numeric($groupId)) {
			$result['error'] = true;
			$result['msg'][] = '更新失败';
			$this->_ajaxReturn($result);
		}
		$data                  = $this->_loadGroupData();
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();

		// 验证数据
		$result = $this->_validateGroupData($data, $groupId);

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		// 更新数据
		$groupModel = $this->_loadModel('category/group');
		if ($groupModel->update($data, $groupId)) {
			$result['msg'][] = '更新成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '更新失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      删除选中分组信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function groupDelAction()
	{
		$groupId = isset($_POST['group_id']) ? $_POST['group_id'] : 0;

		// 验证分组ID是否合法
		if (empty($groupId) || !is_numeric($groupId)) {
			$result['error'] = true;
			$result['msg'][] = '删除失败';
			$this->_ajaxReturn($result);
		}

		// 删除数据的条件
		$where = array('group_id' => array('eq', $groupId));

		// 删除分组的分类产品关系
		$categoryToProductModel = $this->_loadModel('category/to/product');
		$cnt                    = $categoryToProductModel->delByWhere($where);
		$result['msg'][]        = '成功删除' . $cnt . '条分类产品关系';

		// 删除分组的分类数据
		$categoryModel   = $this->_loadModel('category');
		$cnt             = $categoryModel->delByWhere($where);
		$result['msg'][] = '成功删除' . $cnt . '条分类';

		// 删除分组
		$groupModel = $this->_loadModel('category/group');
		if ($groupModel->del($groupId)) {
			$result['msg'][] = '成功删除分组';
		} else {
			$result['error'] = true;
			$result['msg'][] = '删除分组失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取分类信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryAction()
	{
		// 获取分组数据
		$groupId    = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$groupModel = $this->_loadModel('category/group');
		$group      = $groupModel->get($groupId, 'group_id, name');
		if (empty($group)) {
			die('分类分组不合法,非法操作');
		}
		$canAddSubCategory = true;
		$addCategory       = false;
		$showProduct       = false;
		$categoryModel     = $this->_loadModel('category');
		$category = array(
			'group_id'       => $groupId,
			'category_id'    => '0',
			'children_count' => '1',
			'name'           => '',
			'sort'           => '0',
			'status'         => '0',
		);

		// 获取类目树
		$option = array(
			'where' => array('group_id' => array('eq', $groupId)),
			'order' => array('level' => 'ASC', 'sort' => 'ASC')
		);

		// 获取数据
		$categoryList = $categoryModel->getPairs2($option);
		if (!empty($categoryList)) {
			foreach ($categoryList as $val) {
				$this->_data[$val['parent_id']][] = $val;
			}
		}

		if ($categoryId != 0) {
			// 获取分类数据
			$addCategory = true;
			$category    = isset($categoryList[$categoryId]) ? $categoryList[$categoryId] : $category;
			if ($category['group_id'] != $group['group_id']) {
				die('分类不合法,非法操作');
			}
			$where                  = array('category_id' => array('eq', $categoryId));
			$categoryToProductModel = $this->_loadModel('category/to/product');
			$canAddSubCategory      = $categoryToProductModel->getTotalList(array('where' => $where)) ? false : true;
			$showProduct            = $category['children_count'] ? false : true;
		}

		$all[] = array(
			'id'       => 0,
			'text'     => '全部',
			'children' => $this->_buildBranch(0)
		);

		$this->_view->assign('group', $group);
		$this->_view->assign('category', $category);
		$this->_view->assign('canAddSubCategory', $canAddSubCategory);
		$this->_view->assign('showProduct', $showProduct);
		$this->_view->assign('addCategory', $addCategory);
		$this->_view->assign('data', $all);
		$this->_view->render('product/category/index');
	}

	/**
	 * Describe      添加分类
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryAddAction()
	{
		// 获取数据
		$data['group_id']    = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$data['name']        = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['image']       = isset($_POST['image']) ? trim($_POST['image']) : '';
		$data['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';
		$data['status']      = (isset($_POST['status']) && (int)$_POST['status'] > 0) ? 1 : 0;
		$data['sort']        = (isset($_POST['sort']) && $_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['by_added']    = $_SESSION['user_account'];
		$data['date_added']  = now();
		$result = array('error' => false, 'msg' => array());

		// 验证数据
		$categoryModel = $this->_loadModel('category');
		if (!empty($data['parent_id'])) {
			$category = $categoryModel->get($data['parent_id'], 'group_id');
			if (empty($category) || $category['group_id'] != $data['group_id']) {
				$result['error'] = true;
				$result['msg'][] = '分类ID不合法';
			}
		} else {
			$groupModel = $this->_loadModel('category/group');
			if (!$groupModel->validate($data['group_id'])) {
				$result['error'] = true;
				$result['msg'][] = '分组ID不合法';
			}
		}
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分类名称不能为空';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		// 添加数据
		if ($categoryModel->add($data)) {
			$categoryId = $categoryModel->lastInsertId();
			$data = array('path' => $categoryId);

			// 更新数据
			if ($categoryModel->update($data, $categoryId)) {
				$result['category_id'] = $categoryId;
				$result['msg'][]       = '添加成功';
			} else { // 事务回滚
				$categoryModel->del($categoryId);
				$result['error'] = true;
				$result['msg'][] = '添加失败';
			}
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      删除分类信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryDelAction()
	{
		$categoryId    = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$result        = array('error' => false, 'msg' => array());
		$categoryModel = $this->_loadModel('category');
		$category      = $categoryModel->get($categoryId, 'category_id, path, level, children_count');
		if (empty($category)) {
			$result['error'] = true;
			$result['msg'][] = '非法操作';
			$this->_ajaxReturn($result);
		}

		// 删除分类产品关系(含子分类产品关系)
		$where                  = array();
		$categoryToProductModel = $this->_loadModel('category/to/product');
		if ($category['children_count']) {
			$where = array(
				'path'           => array('like', $category['path'] . '/', 'right'),
				'children_count' => array('eq', '0')
			);
			$categoryIds = $categoryModel->getCol(array('where' => $where));
			$where       = array('category_id' => array('IN', $categoryIds));
		}
		$result['msg'][] = '删除分类产品关系:' . $categoryToProductModel->delByWhere($where) . '条';

		// 更新父级分类子级数量
		if ($category['level']) {
			$parentCategoryIds = explode('/', $category['path']);
			array_pop($parentCategoryIds);
			$result['msg'][] = '更新父级分类子级数量:' . $categoryModel->updateParentChildrenCount($parentCategoryIds, '-', $category['children_count'] + 1) . '条';
		}

		// 删除分类的子分类
		if ($category['children_count']) {
			$where           = array('path' => array('like', $category['path'] . '/', 'right'));
			$result['msg'][] = '删除子分类:' . $categoryModel->delByWhere($where) . '条';
		}
		if ($categoryModel->del($category['category_id'])) {
			$result['msg'][] = '分类删除成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '分类删除失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取分类信息
	 * User          黄力军
	 * DateAdded     2016-10-25
	 * DateModified
	 */
	public function categoryGetAction()
	{
		$groupId       = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId    = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$categoryModel = $this->_loadModel('category');
		$option        = array(
			'where' => array(
				'category_id' => array('eq', $categoryId),
				'group_id'    => array('eq', $groupId)
			),
			'col'   => 'name, image, status, sort, description'
		);
		$category = $categoryModel->getRow($option);
		$this->_ajaxReturn($category);
	}

	/**
	 * Describe      修改分类信息
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryUpdateAction()
	{
		$groupId               = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId            = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$data['name']          = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['image']         = isset($_POST['image']) ? trim($_POST['image']) : '';
		$data['description']   = isset($_POST['description']) ? trim($_POST['description']) : '';
		$data['status']        = (isset($_POST['status']) && (int)$_POST['status'] > 0) ? 1 : 0;
		$data['sort']          = (isset($_POST['sort']) && $_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['by_modified']   = $_SESSION['user_account'];
		$data['date_modified'] = now();
		$result = array('error' => false, 'msg' => array());

		// 验证数据
		$where = array(
			'group_id'    => array('eq', $groupId),
			'category_id' => array('eq', $categoryId)
		);
		$categoryModel = $this->_loadModel('category');
		if (!$categoryModel->getTotalList(array('where' => $where))) {
			$result['error'] = true;
			$result['msg'][] = '分组ID或类目ID不合法';
		}
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '分类名称不能为空';
		}

		// 出错返回
		if ($result['error']) {
			$this->_ajaxReturn($result);
		}

		// 更新数据
		if ($categoryModel->update($data, $categoryId)) {
			$result['category_id'] = $categoryId;
			$result['msg'][]       = '更新成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '更新失败';
		}
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      导入所有分类
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryAllUploadAction()
	{
		$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$result  = array('error' => false, 'msg' => array());

		// 超时
		set_time_limit(0);
		@ini_set('post_max_size', '100M');
		@ini_set('upload_max_filesize', '100M');

		$categoryGroupModel = $this->_loadModel('category/group');
		if (!$categoryGroupModel->validate($groupId)) {
			$result['error'] = true;
			$result['msg'][] = '分类分组不存在。';
			$this->_ajaxReturn($result);
		}
		$categoryModel = $this->_loadModel('category');
		$categoryList  = $this->_getCategoryList($groupId);

		// 导入分类
		if (isset($_FILES['uploadallcategory-fl']['error']) || $_FILES['uploadallcategory-fl']['error'] != 4) {
			$categoryFileLocation = isset($_FILES['uploadallcategory-fl']['tmp_name']) ? $_FILES['uploadallcategory-fl']['tmp_name'] : '';
			if (!file_exists($categoryFileLocation)) {
				$result['error'] = true;
				$result['msg'][] = '分类表不存在。';
			} elseif (!($categoryHandle = fopen($categoryFileLocation, 'r'))) {
				$result['error'] = true;
				$result['msg'][] = '分类表无法读取。';
			}
			if (!$result['error']) {
				// 丢弃第一行数据
				fgetcsv($categoryHandle);
				$success = 0;
				$fail    = 0;
				$rowNum  = 1;
				while ($row = fgetcsv($categoryHandle)) {
					$rowNum++;
					$row        = array_map('trim', $row);
					$name       = $row[0];
					$image      = $row[1];
					$parentName = $row[2];
					$sort       = (isset($row[3]) && $row[3] > 0) ? $row[3] : 0;

					// 验证数据
					if (empty($name)) {
						$result['msg'][] = sprintf('分类表第%s行出错,分类名%s不能为空', $rowNum, $name);
						$fail++;
						continue;
					}

					// 判断是否有父分类
					$categoryIsNew = true;
					$sku = (empty($parentName) ? '' : $parentName . '/') . $name;
					if (isset($categoryList[$sku])) {
						$categoryIsNew = false;
						$categoryId    = $categoryList[$sku]['category_id'];
					} else {
						$category = array(
							'category_id'    => 0,
							'level'          => 0,
							'children_count' => 0
						);
						if (!empty($parentName)) {
							if (isset($categoryList[$parentName])) {
								$parentCategory = $categoryList[$parentName];
							} else {
								$result['msg'][] = sprintf('分类表第%s行出错,不存在此父分类%s', $rowNum, $parentName);
								$fail++;
								continue;
							}
							$category          = $parentCategory;
							$category['level'] = $category['level'] + 1;
						}
						$categoryList[$sku] = array(
							'category_id'    => $rowNum,
							'name'           => $name,
							'level'          => $category['level'],
							'children_count' => $category['children_count']
						);
					}

					if (!isset($_GET['debug'])) {
						$data = array(
							'image'         => $image,
							'status'        => 1,
							'sort'          => $sort,
							'by_modified'   => $_SESSION['user_account'],
							'date_modified' => now()
						);
						if ($categoryIsNew) {
							$data['group_id']  = $groupId;
							$data['parent_id'] = $category['category_id'];
							$data['name']      = $name;
							$data['level']     = $category['level'];

							// 添加数据
							if ($categoryModel->add($data)) {
								$tempCategoryId = $categoryModel->lastInsertId();

								// 更新子节点数
								$categoryIds = empty($data['parent_id']) ? $tempCategoryId : explode('/', $category['path']);
								$categoryModel->updateParentChildrenCount($categoryIds);

								// 更新分类树路径
								$path       = empty($data['parent_id']) ? $tempCategoryId : implode('/', array($category['path'], $tempCategoryId));
								$where      = array('category_id' => array('eq', $tempCategoryId));
								$pathUpdate = array('path' => $path);
								if ($categoryModel->updateByWhere($pathUpdate, $where)) {
									$categoryList[$sku] = $categoryModel->get($tempCategoryId, 'category_id, group_id, name, level, path, children_count');
									$success++;
								} else {
									// 事务回滚
									$categoryModel->del($tempCategoryId);
									$result['msg'][] = sprintf('分类表第%s行分类名称:%s添加失败', $rowNum, $name);
									$fail++;
								}
							} else {
								$result['msg'][] = sprintf('分类表第%s行分类名称:%s添加失败', $rowNum, $name);
								$fail++;
							}
						} else {
							if (!$categoryModel->update($data, $categoryId)) {
								$result['msg'][] = sprintf('分类表第%s行分类名称:%s添加失败', $rowNum, $name);
								$fail++;
							}
						}
					}
				}
				$result['msg'][] = sprintf('分类导入 总计:%s,成功:%s,失败:%s', $success + $fail, $success, $fail);
			}
		}

		// 导入分类产品关系
		$this->_uploadAllCategoryToProduct('categoryToProduct-fl', $groupId, $result, $categoryList);

		// 导入副分类产品关系
		$this->_uploadAllCategoryToProduct('subCategory-fl', $groupId, $result, $categoryList, false);

		$result['msg'][] = '导入完成';
		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      导入分类
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function categoryUploadAction()
	{
		$groupId    = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$result	    = array('error' => false, 'msg' => array());

		if (!isset($_FILES['uploadCategory-fl']['error']) || $_FILES['uploadCategory-fl']['error'] == 4) {
			$result['error'] = true;
			$result['msg'][] = '上传的数据不能为空';
			$this->_ajaxReturn($result);
		}

		$categoryFileLocation = isset($_FILES['uploadCategory-fl']['tmp_name']) ? $_FILES['uploadCategory-fl']['tmp_name'] : '';
		if (!file_exists($categoryFileLocation)) {
			$result['error'] = true;
			$result['msg'][] = '文件不存在。';
			$this->_ajaxReturn($result);
		} elseif (!($categoryHandle = fopen($categoryFileLocation, 'r'))) {
			$result['error'] = true;
			$result['msg'][] = '文件无法读取。';
			$this->_ajaxReturn($result);
		}

		if (empty($groupId)) {
			$result['error'] = true;
			$result['msg'][] = '分组ID不合法';
			$this->_ajaxReturn($result);
		}
		if (empty($categoryId)) {
			$result['error'] = true;
			$result['msg'][] = '分类ID不合法';
			$this->_ajaxReturn($result);
		}
		$option = array(
			'where' => array(
				'group_id'    => array('eq', $groupId),
				'category_id' => array('eq', $categoryId)
			)
		);
		$categoryModel  = $this->_loadModel('category');
		$parentCategory = $categoryModel->get($categoryId, 'level, path, group_id');
		if (empty($parentCategory) || $parentCategory['group_id'] != $groupId) {
			$result['msg'][] = '此分组下不存在该分类';
			$this->_ajaxReturn($result);
		}
		$categoryToProductModel = $this->_loadModel('category/to/product');
		if ($categoryToProductModel->getTotalList($option)) {
			$result['msg'][] = '分类下存在产品,不能导入子分类';
			$this->_ajaxReturn($result);
		}

		// 超时
		set_time_limit(0);

		// 丢弃第一行数据
		fgetcsv($categoryHandle);
		$uploadSuccess = 0;
		$uploadFailed  = 0;
		$i             = 1;
		$option = array(
			'where' => array('parent_id' => array('eq', $categoryId)),
			'col'   => 'name'
		);
		$categoryNameList = $categoryModel->getCol($option);
		while ($row = fgetcsv($categoryHandle)) {
			$i++;
			$row   = array_map('trim', $row);
			$name  = $row[0];
			$image = $row[1];
			$sort  = (isset($row[2]) && $row[2] > 0) ? $row[2] : 0;

			// 验证数据
			if (strlen($name) < 1) {
				$result['msg'][] = sprintf('第%s行出错,分类名不能为空', $i);
				$uploadFailed++;
				continue;
			}
			if (in_array($name, $categoryNameList)){
				$result['msg'][] = sprintf('第%s行出错,此分类下已存在子分类%s', $i, $name);
				$uploadFailed++;
				continue;
			}
			$data = array(
				'group_id'       => $groupId,
				'name'           => $name,
				'image'          => $image,
				'parent_id'      => $categoryId,
				'level'          => $parentCategory['level'] + 1,
				'children_count' => 0,
				'status'         => 1,
				'sort'           => $sort,
				'by_added'       => $_SESSION['user_account'],
				'date_added'     => now()
			);
			if ($categoryModel->add($data)) { // 添加数据
				$pathId            = $categoryModel->lastInsertId();
				$path              = array('path' => $parentCategory['path'] . '/' . $pathId);
				$parentCategoryIds = explode('/', $parentCategory['path']);
				$categoryModel->updateParentChildrenCount($parentCategoryIds);
				if ($categoryModel->update($path, $pathId)) {
					$uploadSuccess++;
					continue;
				} else {
					// 事务回滚
					$categoryModel->del($categoryId);
					$result['msg'][] = sprintf('第%s行添加失败', $i);
					$uploadFailed++;
				}
			} else {
				$result['msg'][] = sprintf('第%s行添加失败', $i);
				$uploadFailed++;
			}
		}

		$result['msg'][] = sprintf('总共添加%s个分类', $uploadSuccess + $uploadFailed);
		$result['msg'][] = sprintf('%s个添加成功', $uploadSuccess);
		$result['msg'][] = sprintf('%s个添加失败', $uploadFailed);

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      获取分类产品
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function getCategoryToProductAction()
	{
		$page       = isset($_POST['page']) ? $_POST['page'] : 1;
		$rows       = isset($_POST['rows']) ? $_POST['rows'] : 25;
		$groupId    = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

		// 加载模型
		$categoryModel      = $this->_loadModel('category');
		$categoryGroupModel = $this->_loadModel('category/group');

		$option = array(
			'where' => array('group_id' => array('eq', $groupId)),
			'order' => array('product_id' => 'DESC')
		);
		if ($categoryId) {
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
			$categoryGroup = $categoryGroupModel->get($groupId, 'product_group_id');
			if (empty($categoryGroup)) {
				die('非法访问');
			}

			$where = array('group_id' => array('eq', $categoryGroup['product_group_id']));
			foreach ($_POST['filter'] as $key => $val) {
				switch ($key) {
					case 'status':
					case 'sku':
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

		$categoryToProductModel = $this->_loadModel('category/to/product');
		$total                  = $categoryToProductModel->getTotalList($option);
		$categoryToProductList  = array();
		if ($total) {
			$categoryToProductList = $categoryToProductModel->getList($page, $rows, $option);
			$productIds            = array();
			if (!empty($categoryToProductList)) {
				foreach ($categoryToProductList as $val) {
					$productIds[] = $val['product_id'];
				}
			}
			$option = array(
				'where' => array('product_id' => array('in', $productIds)),
				'order' => array('product_id' => 'DESC'),
				'col'   => 'product_id, sku, image, status'
			);
			$productModel = $this->_loadModel('product');
			$productList  = $productModel->getPairs2($option);

			// 获取分类列表
			$option = array('where' => array('group_id' => array('eq', $groupId)));
			if (!empty($categoryId)) {
				$option['where']['category_id'] = array('in', isset($categoryIds) ? $categoryIds : array($categoryId));
			}
			$categoryList = $categoryModel->getPairs2($option);

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

			foreach ($categoryToProductList as $key => $val) {
				if (isset($productList[$val['product_id']])) {
					$product = $productList[$val['product_id']];
					$categoryToProductList[$key]['sku']      = $product['sku'];
					$categoryToProductList[$key]['image']    = $product['image'];
					$categoryToProductList[$key]['status']   = $product['status'];
					$categoryToProductList[$key]['category'] = $categoryPath[$val['category_id']];
				}
			}
		}
		$this->_ajaxReturn(array('total' => $total, 'rows' => $categoryToProductList));
	}

	/**
	 * Describe      添加分类产品
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 */
	public function productAddAction()
	{
		$groupId    = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
		$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
		$sku        = isset($_POST['sku']) ? trim($_POST['sku']) : '';
		$sort       = (isset($_POST['sort']) && (int)$_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$result     = array('error' => false, 'msg' => array());

		// 验证sku
		if (strlen($sku) < 1) {
			$result['error'] = true;
			$result['msg'][] = '产品型号不行为空';
			$this->_ajaxReturn($result);
		}

		// 获取产品分组数据
		$categoryGroupModel = $this->_loadModel('category/group');
		$categoryGroup      = $categoryGroupModel->get($groupId, 'product_group_id');
		if (empty($categoryGroup)) {
			$result['error'] = true;
			$result['msg'][] = '产品分组不存在';
			$this->_ajaxReturn($result);
		}

		// 验证产品
		$option = array(
			'where' => array(
				'sku'      => array('eq', $sku),
				'group_id' => array('eq', $categoryGroup['product_group_id'])
			)
		);
		$productModel = $this->_loadModel('product');
		$product      = $productModel->getRow($option);
		if (empty($product)) {
			$result['error'] = true;
			$result['msg'][] = '该产品分组中不存在该产品';
			$this->_ajaxReturn($result);
		}

		// 判断是否重复添加
		$option = array(
			'where' => array(
				'product_id'  => array('eq', $product['product_id']),
				'category_id' => array('eq', $categoryId)
			)
		);
		$categoryToProductModel = $this->_loadModel('category/to/product');
		if ($categoryToProductModel->getTotalList($option)) {
			$result['error'] = true;
			$result['msg'][] = '分类中已存在此产品';
			$this->_ajaxReturn($result);
		}

		// 判断是否主分类
		$option = array(
			'where' => array(
				'product_id' => array('eq', $product['product_id']),
				'group_id'   => array('eq', $groupId),
				'is_master'  => array('eq', 1)
			)
		);
		$isMaster = $categoryToProductModel->getTotalList($option) ? 0 : 1;

		// 添加数据
		$data = array(
			'group_id'    => $groupId,
			'category_id' => $categoryId,
			'is_master'   => $isMaster,
			'sort'        => $sort,
			'product_id'  => $product['product_id'],
			'by_added'    => $_SESSION['user_account'],
			'date_added'  => now(),
			'version'     => date('Ymd')
		);
		if ($categoryToProductModel->add($data)) {
			$result['msg'][] = '添加成功';
		} else {
			$result['error'] = true;
			$result['msg'][] = '添加失败';
		}

		$this->_ajaxReturn($result);
	}

	/**
	 * Describe      生成分类树
	 * User          黄力军
	 * DateAdded     2016-8-12
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

	/**
	 * Describe      加载分组数据
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 *
	 * @return mixed
	 */
	private function _loadGroupData()
	{
		$data['name']    = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['sort']    = (isset($_POST['sort']) && (int)$_POST['sort'] > 0) ? (int)$_POST['sort'] : 0;
		$data['remarks'] = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
		return $data;
	}

	/**
	 * Describe      验证分组数据
	 * User          黄力军
	 * DateAdded     2016-8-12
	 * DateModified
	 *
	 * @param $data
	 * @param int $groupId
	 * @return array
	 */
	private function _validateGroupData($data, $groupId = 0)
	{
		$result     = array('error' => false, 'msg' => array());
		$groupModel = $this->_loadModel('category/group');
		if (empty($data['name'])) {
			$result['error'] = true;
			$result['msg'][] = '方案名称不能为空';
		} elseif ($groupModel->existName($data['name'], $groupId)) {
			$result['error'] = true;
			$result['msg'][] = '方案名称已存在';
		}
		return $result;
	}

	/**
	 * Describe     获取分类列表数据以路径作为键名
	 * User         陈伟义
	 * DateAdded    2016-09-22
	 * DateModified
	 *
	 * @param $groupId
	 * @return array
	 */
	private function _getCategoryList($groupId)
	{
		// 获取已分类ID为键名的分组信息
		$option        = array('where' => array('group_id' => array('eq', $groupId)));
		$categoryModel = $this->_loadModel('category');
		$tempList      = $categoryModel->getPairs2($option);
		$categoryList  = array();
		if (empty($tempList)) {
			return $categoryList;
		}
		foreach ($tempList as $key => $val) {
			// 将路径分解成一个分类ID的数组
			$tempIds = explode('/', $tempList[$key]['path']);
			$temp    = array();

			// 通过分类ID取得分类名
			foreach ($tempIds as $v) {
				$temp[$v] = $tempList[$v]['name'];
			}
			ksort($temp);
			$temp = implode('/', $temp);
			$categoryList[$temp] = $val;
		}

		return $categoryList;
	}

	/**
	 * Describe     在导入分类时导入分类产品关系
	 * User         陈伟义
	 * DateAdded    2016-09-22
	 * DateModified
	 *
	 * @param $handle
	 * @param $categoryGroup
	 * @param $categoryList
	 * @param $result
	 * @param bool $isMaster
	 * @param $debug
	 * @return bool
	 */
	private function _uploadCategoryOfProduct($handle, $categoryGroup, $categoryList, &$result, $isMaster = true, $debug = false)
	{
		// 参数
		$version = date('Ymd');
		$title   = $isMaster ? '主分类产品' : '副分类产品';

		// 获取该产品分组下的所有产品
		$productModel = $this->_loadModel('product');
		$option = array(
			'where'	=> array('group_id' => array('eq', $categoryGroup['product_group_id'])),
			'col'	=> 'sku, product_id'
		);
		$productList = $productModel->getPairs($option);
		if (empty($productList)) {
			$result['msg'][] = sprintf('%s导入 暂无产品,%s导入失败', $title, $title);
			return false;
		}

		$uploadSuccess = 0;
		$uploadFailed  = 0;
		$rowNum        = 1;
		$categoryToProductModel = $this->_loadModel('category/to/product');
		while ($row = fgetcsv($handle)) {
			$rowNum++;
			$row  = array_map('trim', $row);
			$sku  = $row[0];
			$path = $row[1];
			$sort = $row[2] > 0 ? (int)$row[2] : 0;

			// 验证数据
			if (empty($sku)) {
				$result['msg'][] = sprintf('%s表第%s行出错,产品型号不能为空', $title, $rowNum);
				$uploadFailed++;
				continue;
			}
			if (!isset($productList[$sku])) {
				$result['msg'][] = sprintf('%s表第%s行出错,产品:%s不存在', $title, $rowNum, $sku);
				$uploadFailed++;
				continue;
			}

			if (!empty($path)) { // 分类路径不为空
				if (!isset($categoryList[$path])) {
					$result['msg'][] = sprintf('%s表第%s行出错,不存在分类%s', $title, $rowNum, $path);
					$uploadFailed++;
					continue;
				} elseif ($categoryList[$path]['children_count'] > 0) {
					$result['msg'][] = sprintf('%s表第%s行出错,%s分类存在子分类,不能添加产品%s', $title, $rowNum, $path, $sku);
					$uploadFailed++;
					continue;
				}
			}

			if (!$debug) { // 不是测试模式
				$where = array(
					'product_id' => array('eq', $productList[$sku]),
					'group_id'   => array('eq', $categoryGroup['group_id'])
				);
				if (!$isMaster) { // 主分类产品删除产品的所有分类关系,副分类产品,删除产品的所有副分类关系
					$where['is_master'] = array('eq', '0');
				}
				$categoryToProductModel->delByWhere($where);
			} else {
				$result['msg'][] = sprintf('将删除产品型号:%s的所有分类产品关系', $sku);
			}

			if (empty($path)) {
				continue;
			}

			$categoryId = $categoryList[$path]['category_id'];

			// 添加副分类产品时验证主分类产品是否存在
			$productId = $productList[$sku];
			if (!$isMaster) {
				$option = array(
					'where' => array(
						'product_id' => array('eq', $productId),
						'is_master'  => array('eq', 1),
						'group_id'   => array('eq', $categoryGroup['group_id'])
					),
					'col'   => 'category_id'
				);
				$categoryToProduct = $categoryToProductModel->getRow($option);
				if (empty($categoryToProduct)) {
					$result['msg'][] = sprintf('%s表第%s行出错,主分类中不存在产品%s,不能添加副分类产品', $title, $rowNum, $sku);
					$uploadFailed++;
					continue;
				}
				if ($categoryId == $categoryToProduct['category_id']) {
					$result['msg'][] = sprintf('%s表第%s行出错,副分类产品%s已添加为该分类的主分类产品', $title, $rowNum, $sku);
					$uploadFailed++;
					continue;
				}
			}

			if ($debug) {
				continue;
			}

			// 添加数据
			$data = array(
				'product_id'  => $productId,
				'group_id'    => $categoryGroup['group_id'],
				'category_id' => $categoryId,
				'is_master'   => $isMaster,
				'sort'        => $sort,
				'by_added'    => $_SESSION['user_account'],
				'date_added'  => now(),
				'version'     => $version
			);

			if ($categoryToProductModel->add($data)) {
				$uploadSuccess++;
				continue;
			} else {
				$result['msg'][] = $sku . ' 添加失败';
				$uploadFailed++;
			}
		}
		$result['msg'][] = sprintf('%s导入 总计:%s,成功:%s,失败:%s', $title, $uploadSuccess + $uploadFailed, $uploadSuccess, $uploadFailed);
	}

	/**
	 * Describe      导入主副分类产品
	 * User          黄力军
	 * DateAdded     2016-10-24
	 * DateModified
	 * @param $fileName
	 * @param $groupId
	 * @param $result
	 * @param $categoryList
	 * @param bool $isMaster
	 */
	private function _uploadAllCategoryToProduct($fileName, $groupId, &$result, $categoryList, $isMaster = true)
	{
		$title = $isMaster ? '主分类' : '副分类';
		if (isset($_FILES[$fileName]['error']) || $_FILES[$fileName]['error'] != 4) {
			$result['error'] = false;
			$fileLocation    = isset($_FILES[$fileName]['tmp_name']) ? $_FILES[$fileName]['tmp_name'] : '';
			if (!file_exists($fileLocation)) {
				$result['error'] = true;
				$result['msg'][] = $title . '产品表不存在。';
			} elseif (!($handle = fopen($fileLocation, 'r'))) {
				$result['error'] = true;
				$result['msg'][] = $title . '产品表无法读取。';
			}
			if (!$result['error']) {
				// 丢弃第一行数据
				fgetcsv($handle);
				$categoryGroupModel = $this->_loadModel('category/group');
				$categoryGroup      = $categoryGroupModel->get($groupId, 'group_id, product_group_id');
				if (isset($_GET['debug'])) {
					$this->_uploadCategoryOfProduct($handle, $categoryGroup, $categoryList, $result, $isMaster, true);
				} else {
					$this->_uploadCategoryOfProduct($handle, $categoryGroup, $categoryList, $result, $isMaster);
				}
			}
		}
	}
}