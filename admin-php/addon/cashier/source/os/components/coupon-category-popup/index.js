import {
	getGoodsCategory
} from '@/api/goods.js'
export default {
	name: 'couponCategoryPopup',
	data() {
		return {
			treeData: [],
			defaultCheckedKeysValue: [],
			checkList: [], //选中以及半选中数据

		}
	},
	mounted() {
		this.getGoodsCategoryFn()
	},
	methods: {
		getGoodsCategoryFn() {
			getGoodsCategory({
				level: 3
			}).then(res => {
				this.treeData = res.data
			})
		},
		open(value) {
			this.defaultCheckedKeysValue = this.$util.deepClone(value)
			this.$refs.couponCategoryPop.open()
		},
		handleTreeChange(val) {
			this.defaultCheckedKeysValue = this.$util.deepClone(val)
			let halfCheckList = this.$refs.DaTreeRef.getHalfCheckedKeys()||[]
			this.checkList = this.$util.deepClone(val.concat(halfCheckList))
		},
		//处理数据
		getSelectedIdsAndNames(tree_selected, tree_all) {
			let name_arr = [];
			let id_arr = [];
			let selected_num = 0;
			for (let i in tree_selected) {
				let item_selected = tree_selected[i];
				let item_all = null;
				tree_all.forEach((item) => {
					if (item.category_id === item_selected.category_id) {
						item_all = item;
						return;
					}
				})
				if (!item_all) throw '对比数据有误';
				let title = item_selected.category_name;
				id_arr.push(item_selected.category_id);
				if (item_selected.child_num > 0) {
					let res = this.getSelectedIdsAndNames(item_selected.child_list, item_all.child_list);
					if (res.selected_num == item_all.child_num) {
						selected_num++;
					} else {
						title += '（' + res.name_arr.join('、') + '）';
					}
					id_arr = id_arr.concat(res.id_arr);
				} else {
					selected_num++;
				}
				name_arr.push(title);
			}
			return {
				selected_num: selected_num,
				name_arr: name_arr,
				id_arr: id_arr,
			};
		},
		confirm() {
			if(!this.checkList.length){
				this.$util.showToast({
					title: "请选择商品分类"
				});
				return false
			}
			getGoodsCategory({
				level: 3,
				category_ids:this.checkList.join(',')
			}).then(res => {
				let selectedData = this.getSelectedIdsAndNames(res.data, this.treeData);
				this.$emit('confirm',selectedData)
				this.$refs.couponCategoryPop.close()
			})
		}
	}
}