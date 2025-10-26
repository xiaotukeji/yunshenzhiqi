<template>
	<view class="journal">
		<view class="item" v-for="(item, index) in list" :key="index">
			<view class="time">
				<view>{{ $util.timeFormat(item.action_time).split(' ')[0] }}</view>
				<view>{{ $util.timeFormat(item.action_time).split(' ')[1] }}</view>
			</view>
			<view class="unit">
				<view class="top">
					<view class="core"></view>
					<view class="unit-separate"></view>
				</view>
			</view>
			<view class="message">{{ item.action }}</view>
		</view>
	</view>
</template>

<script>
	export default {
		props: {
			list: {
				type: Array,
				default: function() {
					return [];
				}
			}
		},
		data() {
			return {};
		},
		mounted() {},
		methods: {}
	};
</script>

<style scoped lang="scss">
	.journal {
		padding-left: 0.1rem;
		box-sizing: border-box;

		.item {
			width: 100%;
			height: 0.7rem;
			display: flex;

			.time {
				margin-right: 0.1rem;
				min-width: 1rem;

				view:nth-child(1) {
					font-size: 0.16rem;
					margin-bottom: 0.1rem;
					text-align: right;
				}

				view:nth-child(2) {
					font-size: 0.14rem;
					color: #999999;
					text-align: right;
				}
			}
		}

		.unit {
			width: 0.18rem;
			height: 100%;
			margin-right: 0.1rem;

			.top {
				width: 0.18rem;
				height: 0.18rem;
				border-radius: 50%;
				background: $primary-color;
				position: relative;

				.core {
					background: #ffffff;
					width: 0.08rem;
					height: 0.08rem;
					border-radius: 50%;
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
					z-index: 999;
				}

				.unit-separate {
					position: absolute;
					width: 0.01rem;
					height: 0.7rem;
					top: 0;
					left: 50%;
					transform: translateX(-50%);
					background: $primary-color;
					z-index: 555;
				}
			}
		}

		.message {
			font-size: 0.14rem;
		}
	}
</style>