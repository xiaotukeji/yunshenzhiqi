<template>
	<view class="uni-calendar-item__weeks-box" :class="{
		'uni-calendar-item--disable':weeks.disable,
		'uni-calendar-item--before-checked-x':weeks.beforeMultiple,
		'uni-calendar-item--multiple': weeks.multiple,
		'uni-calendar-item--after-checked-x':weeks.afterMultiple,
		}" @click="choiceDate(weeks)" @mouseenter="handleMousemove(weeks)">
		<view class="uni-calendar-item__weeks-box-item" :class="{
				'uni-calendar-item--checked':calendar.fullDate === weeks.fullDate && (calendar.userChecked || !checkHover),
				'uni-calendar-item--checked-range-text': checkHover,
				'uni-calendar-item--before-checked':weeks.beforeMultiple,
				'uni-calendar-item--multiple': weeks.multiple,
				'uni-calendar-item--after-checked':weeks.afterMultiple,
				'uni-calendar-item--disable':weeks.disable,
				}">
			<text v-if="selected&&weeks.extraInfo" class="uni-calendar-item__weeks-box-circle"></text>
			<text class="uni-calendar-item__weeks-box-text uni-calendar-item__weeks-box-text-disable uni-calendar-item--checked-text">{{weeks.date}}</text>
		</view>
		<view :class="{'uni-calendar-item--isDay': weeks.isDay}"></view>
	</view>
</template>

<script>
	export default {
		props: {
			weeks: {
				type: Object,
				default () {
					return {}
				}
			},
			calendar: {
				type: Object,
				default: () => {
					return {}
				}
			},
			selected: {
				type: Array,
				default: () => {
					return []
				}
			},
			lunar: {
				type: Boolean,
				default: false
			},
			checkHover: {
				type: Boolean,
				default: false
			}
		},
		methods: {
			choiceDate(weeks) {
				this.$emit('change', weeks)
			},
			handleMousemove(weeks) {
				this.$emit('handleMouse', weeks)
			}
		}
	}
</script>

<style lang="scss" scoped>
	.uni-calendar-item__weeks-box {
		flex: 1;
		/* #ifndef APP-NVUE */
		display: flex;
		/* #endif */
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin: 0.01rem 0;
		position: relative;
	}

	.uni-calendar-item__weeks-box-text {
		font-size: 0.14rem;
		// font-family: Lato-Bold, Lato;
		font-weight: bold;
		color: #455997;
	}

	.uni-calendar-item__weeks-lunar-text {
		font-size: 0.12rem;
		color: #333;
	}

	.uni-calendar-item__weeks-box-item {
		position: relative;
		/* #ifndef APP-NVUE */
		display: flex;
		/* #endif */
		flex-direction: column;
		justify-content: center;
		align-items: center;
		width: 0.4rem;
		height: 0.4rem;
		/* #ifdef H5 */
		cursor: pointer;
		/* #endif */
	}

	.uni-calendar-item__weeks-box-circle {
		position: absolute;
		top: 0.05rem;
		right: 0.05rem;
		width: 0.08rem;
		height: 0.08rem;
		border-radius: 0.08rem;
		background-color: #dd524d;
	}

	.uni-calendar-item__weeks-box .uni-calendar-item--disable {
		// background-color: rgba(249, 249, 249, $uni-opacity-disabled);
		cursor: default;
	}

	.uni-calendar-item--disable .uni-calendar-item__weeks-box-text-disable {
		color: #D1D1D1;
	}

	.uni-calendar-item--isDay {
		position: absolute;
		top: 0.1rem;
		right: 17%;
		background-color: #dd524d;
		width: 0.06rem;
		height: 0.06rem;
		border-radius: 50%;
	}

	.uni-calendar-item--extra {
		color: #dd524d;
		opacity: 0.8;
	}

	.uni-calendar-item__weeks-box .uni-calendar-item--checked {
		background-color: $primary-color;
		border-radius: 50%;
		box-sizing: border-box;
		border: 0.03rem solid #fff;
	}

	.uni-calendar-item--checked .uni-calendar-item--checked-text {
		color: #fff;
	}

	.uni-calendar-item--multiple .uni-calendar-item--checked-range-text {
		color: #333;
	}

	.uni-calendar-item--multiple {
		background-color:  #F6F7FC;
		// color: #fff;
	}

	.uni-calendar-item--multiple .uni-calendar-item--before-checked,
	.uni-calendar-item--multiple .uni-calendar-item--after-checked {
		background-color: $primary-color;
		border-radius: 50%;
		box-sizing: border-box;
		border: 0.03rem solid #F6F7FC;
	}

	.uni-calendar-item--before-checked .uni-calendar-item--checked-text,
	.uni-calendar-item--after-checked .uni-calendar-item--checked-text {
		color: #fff;
	}

	.uni-calendar-item--before-checked-x {
		border-top-left-radius: 0.5rem;
		border-bottom-left-radius: 0.5rem;
		box-sizing: border-box;
		background-color: #F6F7FC;
	}

	.uni-calendar-item--after-checked-x {
		border-top-right-radius: 0.5rem;
		border-bottom-right-radius: 0.5rem;
		background-color: #F6F7FC;
	}
</style>
