const addressData = require('china-area-data/v3/data');
import _ from 'lodash';

// 注册名为 select-district 的 Vue 组件
Vue.component('select-district', {
    // 定义组件属性
    props: {
        // 初始化，在编辑时会用到
        initValue: {
            type: Array,
            default: () => ([]), // 默认为空数组
        }
    },

    // 定义组件内的数据
    data() {
        return {
            provinces: addressData['86'],
            cities: {},
            districts: {},
            provinceId: '', // 当前选中的省
            cityId: '',
            districtId: '',
        };
    },
    // 定义观察器，对应属性变更时会触发对应的观察器函数
    watch: {
        // 当选择的省发生改变时触发
        provinceId(newVal) {
            if (!newVal) {
                this.cities = {};
                this.cityId = '';
                return;
            }
            // 将城市列表设为当前省的城市
            this.cities = addressData[newVal];
            // 若当前选中的城市不在当前省下，则清空选中的城市
            if (!this.cities[this.cityId]) {
                this.cityId = '';
            }
        },
        // 当选择的市发送改变时触发
        cityId(newVal) {
            if (!newVal) {
                this.districts = {};
                this.districtId = '';
                return;
            }
            this.districts = addressData[newVal];
            if (!this.districts[this.districtId]) {
                this.districtId = '';
            }
        },
        // 当选择的区发生改变时触发
        districtId() {
            // 触发一个名为 change 的 Vue 事件，事件的值就是当前选中省市区名称，格式为数组
            this.$emit('change', [this.provinces[this.provinceId], this.cities[this.cityId], this.districts[this.districtId]]);
        },
    },
    // 组件初始化时调用这个方法
    created() {
        this.setFromValue(this.initValue);
    },
    methods: {
        setFromValue(value) {
            // 过滤掉空值
            value = _.filter(value);
            // 如果数组为空，则清空省，市和区会随着触发器也被清空
            if (value.length === 0) {
                this.provinceId = '';
                return;
            }
            // 从当前省列表中找到与数组第一个元素同名的项的索引
            const provinceId = _.findKey(this.provinces, o => o === value[0]);
            if (!provinceId) {
                this.provinceId = '';
                return;
            }
            this.provinceId = provinceId;
            // 由于观察器的作用，这时候城市列表会随之改变
            const cityId = _.findKey(addressData[provinceId], o => o === value[1]);
            if (!cityId) {
                this.cityId = '';
                return;
            }
            this.cityId = cityId;
            // 相应的，找寻区的值
            const districtId = _.findKey(addressData[cityId], o => o === value[2]);
            if (!districtId) {
                this.districtId = '';
                return;
            }
            this.districtId = districtId;
        }
    }
});
