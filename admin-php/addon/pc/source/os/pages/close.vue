<template>
    <div class="error-wrap">
		<img src="@/assets/images/index/closed.png" />
        <!-- <i class="iconfont icon404"></i> -->
        <h2>{{ '网站已关闭' }}</h2>
    </div>
</template>

<script>
import { getSiteStatus } from '@/api/website';

export default {
  name: "close",
  created() {
		this.getSiteStatus();
  },
  layout: 'empty',
	methods:{
		getSiteStatus(){
			getSiteStatus().then((res) => {
				if (res.code == 0) {
					this.$router.push('/');
				}
			})
		}
	}
}
</script>
<style lang="scss" scoped>
.error-wrap {
    text-align: center;
    padding: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    i {
        font-size: 60px;
    }
    h2 {
        margin: 0 0 30px;
    }
}
</style>
