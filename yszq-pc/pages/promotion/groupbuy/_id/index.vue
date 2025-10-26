<template>
  <div class="goods-detail-wrap">
    <div class="detail-nav-wrap" v-if="categoryNameArr">
      <div class="detail-nav" v-if="categoryNameArr.length">
        <template v-for="(item,index) in categoryNameArr" :keys="index">
          <router-link :to="{ path: '/goods/list', query: { category_id: item.category_id, level: index } }">
            {{item.name}}
          </router-link>
          <span class="iconfont icon-arrow-right"></span>
        </template>
        <span class="goods-name">{{goodsSkuDetail.goods_name}}</span>
      </div>
    </div>
    <div class="detail-main">
      <div class="goods-detail">
        <div class="preview-wrap">
          <div class="video-player-wrap" :class="{ show: switchMedia == 'video' }" v-if="goodsSkuDetail.video_url != ''">
            <video-player v-if="goodsSkuDetail.video_url != ''" ref="videoPlayer" :playsinline="true"
                          :options="playerOptions" @play="onPlayerPlay($event)" @pause="onPlayerPause($event)"
                          @ended="onPlayerEnded($event)" @waiting="onPlayerWaiting($event)"
                          @playing="onPlayerPlaying($event)"
                          @loadeddata="onPlayerLoadeddata($event)" @timeupdate="onPlayerTimeupdate($event)"
                          @canplay="onPlayerCanplay($event)" @canplaythrough="onPlayerCanplaythrough($event)"
                          @statechanged="playerStateChanged($event)" @ready="playerReadied"></video-player>

            <div class="media-mode" v-if="goodsSkuDetail.video_url != ''">
              <span :class="{ 'ns-bg-color': switchMedia == 'video' }" @click="switchMedia = 'video'">视频</span>
              <span :class="{ 'ns-bg-color': switchMedia == 'img' }" @click="switchMedia = 'img'">图片</span>
            </div>
          </div>
          <div class="magnifier-wrap">
            <pic-zoom ref="PicZoom" :url="$img(picZoomUrl)" :scale="2"></pic-zoom>
          </div>

          <div class="spec-items">
            <span class="left-btn iconfont icon-weibiaoti35" :class="{ move: moveThumbLeft }" @click="changeThumbImg('prev')"></span>
            <span class="right-btn iconfont icon-weibiaoti35" :class="{ move: moveThumbRight }" @click="changeThumbImg('next')"></span>
            <ul :style="{ top: 42 + thumbPosition + 'px' }">
              <!-- 商品缩率图 -->
              <li v-for="(item, index) in goodsSkuDetail.sku_images" :key="index" @mousemove="picZoomUrl = item" :class="{ selected: picZoomUrl == item }">
                <img :src="$img(item, { size: 'small' })" @error="imageErrorSpec(index)" />
              </li>
            </ul>
          </div>
        </div>

        <!-- 商品信息 -->
        <div class="basic-info-wrap" v-loading="loading">
          <h1>{{ goodsSkuDetail.sku_name }}</h1>
          <p class="desc ns-text-color" v-if="goodsSkuDetail.introduction">{{ goodsSkuDetail.introduction }}</p>

          <div class="discount-banner ns-bg-color" v-if="groupbuyTimeMachine.currentTime">
            <div class="activity-name">
              <i class="discount-icon iconfont iconicon_naozhong"></i>
              <span>团购</span>
            </div>
            <div class="surplus-time">
              <span>{{ groupbuyText }}</span>
              <count-down class="count-down" v-on:start_callback="countDownS_cb()" v-on:end_callback="countDownE_cb()"
                          :currentTime="groupbuyTimeMachine.currentTime" :startTime="groupbuyTimeMachine.startTime"
                          :endTime="groupbuyTimeMachine.endTime" :dayTxt="'天'" :hourTxt="'小时'" :minutesTxt="'分钟'"
                          :secondsTxt="'秒'">
              </count-down>
            </div>
          </div>

          <div class="item-block">
            <div class="promotion-price">
              <dl class="item-line">
                <dt class="ns-text-color-gray">团购价</dt>
                <dd>
                  <em class="yuan ns-text-color">¥</em>
                  <span class="price ns-text-color">{{ goodsSkuDetail.groupbuy_price }}</span>
                </dd>
              </dl>
              <dl class="item-line">
                <dt class="ns-text-color-gray">原价</dt>
                <dd>
                  <em class="market-yuan">¥</em>
                  <span class="market-price">{{ goodsSkuDetail.price }}</span>
                </dd>
              </dl>

              <!-- <div class="statistical">
      				<ul>
      					<li>
      						<p>累计评价</p>
      						<span>{{ goodsSkuDetail.evaluate }}</span>
      					</li>
      					<li>
      						<p>累计销量</p>
      						<span>{{ goodsSkuDetail.sale_num }}{{ goodsSkuDetail.unit }}</span>
      					</li>
      				</ul>
      			</div> -->

              <dl class="item-line" v-if="goodsSkuDetail.is_virtual == 0">
                <dt>运费</dt>
                <dd>
                  <i class="i-activity-flag ns-text-color ns-border-color" v-if="goodsSkuDetail.is_free_shipping">快递免邮</i>
                  <i class="i-activity-flag ns-text-color ns-border-color" v-else>快递不免邮</i>
                </dd>
              </dl>
            </div>
          </div>
          <dl class="item-line delivery" v-if="goodsSkuDetail.is_virtual == 0">
            <dt>配送至</dt>
            <dd>
              <div class="region-selected ns-border-color-gray">
                <span>
                  <template v-if="selectedAddress['level_1']">
                    <template v-for="item in selectedAddress">{{ item.name }}</template>
                  </template>
                  <template v-else>请选择配送地址</template>
                </span>
                <i class="el-icon-arrow-down"></i>
              </div>

              <div class="region-list ns-border-color-gray" :class="{ hide: hideRegion }">
                <ul class="nav-tabs">
                  <li :class="{ active: currTabAddres == 'province' }" @click="currTabAddres = 'province'">
                    <div>
                      <span>{{ selectedAddress['level_1'] ? selectedAddress['level_1'].name : '请选择省' }}</span>
                      <i class="el-icon-arrow-down"></i>
                    </div>
                  </li>
                  <li :class="{ active: currTabAddres == 'city' }" @click="currTabAddres = 'city'">
                    <div>
                      <span>{{ selectedAddress['level_2'] ? selectedAddress['level_2'].name : '请选择市' }}</span>
                      <i class="el-icon-arrow-down"></i>
                    </div>
                  </li>
                  <li :class="{ active: currTabAddres == 'district' }" @click="currTabAddres = 'district'">
                    <div>
                      <span>{{ selectedAddress['level_3'] ? selectedAddress['level_3'].name : '请选择区/县' }}</span>
                      <i class="el-icon-arrow-down"></i>
                    </div>
                  </li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane" :class="{ active: currTabAddres == 'province' }">
                    <ul class="province">
                      <li v-for="(item, index) in provinceArr" :key="index" :class="{ selected: selectedAddress['level_' + item.level] && selectedAddress['level_' + item.level].id == item.id }">
                        <span @click="getAddress('city', item)">{{ item.name }}</span>
                      </li>
                    </ul>
                  </div>
                  <div class="tab-pane" :class="{ active: currTabAddres == 'city' }">
                    <ul class="city">
                      <li v-for="(item, index) in cityArr" :key="index" :class="{ selected: selectedAddress['level_' + item.level] && selectedAddress['level_' + item.level].id == item.id }">
                        <span @click="getAddress('district', item)">{{ item.name }}</span>
                      </li>
                    </ul>
                  </div>
                  <div class="tab-pane" :class="{ active: currTabAddres == 'district' }">
                    <ul class="district">
                      <li v-for="(item, index) in districtArr" :key="index" :class="{ selected: selectedAddress['level_' + item.level] && selectedAddress['level_' + item.level].id == item.id }">
                        <span @click="getAddress('community', item)">{{ item.name }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </dd>
          </dl>
          <dl class="item-line service">
            <dt>服务</dt>
            <dd>
              <span>
                由
                <span class="ns-text-color">{{siteInfo.site_name}}</span>
                发货并提供售后服务
              </span>
            </dd>
          </dl>
          <hr class="divider" />
          <div class="sku-list" v-if="goodsSkuDetail.goods_spec_format">
            <dl class="item-line" v-for="(item, index) in goodsSkuDetail.goods_spec_format" :key="index">
              <dt>{{ item.spec_name }}</dt>
              <dd>
                <ul>
                  <li v-for="(item_value, index_value) in item.value" :key="index_value">
                    <div :class="{
      								'selected ns-border-color': item_value['selected'] || skuId == item_value.sku_id,
      								disabled: item_value['disabled'] || (!item_value['selected'] && specDisabled)
      							}" @click="changeSpec(item_value.sku_id, item_value.spec_id)">
                      <img v-if="item_value.image" :src="$img(item_value.image, { size: 'small' })" />
                      <span>{{ item_value.spec_value_name }}</span>
                      <i class="iconfont iconduigou1 ns-text-color"></i>
                    </div>
                  </li>
                </ul>
              </dd>
            </dl>
          </div>

          <div class="buy-number">
            <dl class="item-line">
              <dt>数量</dt>
              <dd>
                <div class="num-wrap">
                  <div class="operation">
                    <span class="decrease el-icon-minus" @click="changeNum('-')"></span>
                    <el-input v-model="number" placeholder="0" @input="keyInput()"></el-input>
                    <span class="increase el-icon-plus" @click="changeNum('+')"></span>
                  </div>
                </div>
                <span class="unit">{{ goodsSkuDetail.unit }}</span>
                <span class="inventory">库存{{ goodsSkuDetail.stock }}{{ goodsSkuDetail.unit }}</span>
                <!-- 限购 -->
                <em v-if="limitNumber > 0">({{ limitNumber }}{{ goodsSkuDetail.unit }}起购)</em>
              </dd>
            </dl>
          </div>

          <dl class="item-line buy-btn">
            <dt></dt>
            <dd v-if="goodsSkuDetail.goods_state == 1">
              <template v-if="goodsSkuDetail.goods_stock < goodsSkuDetail.buy_num && !goodsSkuDetail.sku_spec_format">
                <el-button type="info" plain disabled>库存不足</el-button>
              </template>
              <template v-else>
                <el-button type="primary" plain @click="buyNow">立即抢购</el-button>
              </template>
              <div class="go-phone icon-item" @click="editCollection">
                <span :class="['iconfont',(whetherCollection == 1 ? 'icon-_shouzang2 selected' : 'icon-shouzang')]"></span>
                <span>收藏</span>
              </div>
            </dd>

            <dd v-else>
              <template>
                <el-button type="info" plain disabled>该商品已下架</el-button>
              </template>
              <div class="go-phone icon-item" @click="editCollection">
                <span :class="['iconfont',(whetherCollection == 1 ? 'icon-_shouzang2 selected' : 'icon-shouzang')]"></span>
                <span>收藏</span>
              </div>
            </dd>

          </dl>
          <dl class="item-line merchant-service" v-show="service_list.length">
            <dt>商品服务</dt>
            <div>
              <dd class="service-li" v-for="item in service_list">
                <i class="el-icon-success"></i>
                <span class="ns-text-color-gray" :title="item.service_name">{{item.service_name}}</span>
              </dd>
            </div>
          </dl>
        </div>

        <div class="detail-wrap">

          <el-tabs class="goods-tab" v-model="tabName" type="card" @tab-click="tabChange">
            <el-tab-pane label="商品详情" name="detail">
              <div v-html="goodsSkuDetail.goods_content"></div>
            </el-tab-pane>
            <el-tab-pane label="商品属性" name="attr">
              <ul class="attr-list">
                <template v-if="goodsSkuDetail.goods_attr_format && goodsSkuDetail.goods_attr_format.length > 0">
                  <li v-for="(item, index) in goodsSkuDetail.goods_attr_format" :key="index">{{ item.attr_name }}：{{ item.attr_value_name }}</li>
                </template>
              </ul>
            </el-tab-pane>
            <el-tab-pane v-if="evaluate_show" :label="goodsEvaluateList.length ? '商品评价(' + goodsEvaluateList.length + ')' : '商品评价'" name="evaluate" class="evaluate">
              <template v-if="goodsEvaluateList.length">
                <nav>
                  <li :class="evaluaType == 0 ? 'selected' : ''" @click="evaluationType(0)">全部评价({{evaluteCount.total}})</li>
                  <li :class="evaluaType == 1 ? 'selected' : ''" @click="evaluationType(1)">好评({{evaluteCount.haoping}})</li>
                  <li :class="evaluaType == 2 ? 'selected' : ''" @click="evaluationType(2)">中评({{evaluteCount.zhongping}})</li>
                  <li :class="evaluaType == 3 ? 'selected' : ''" @click="evaluationType(3)">差评({{evaluteCount.chaping}})</li>
                </nav>
                <ul class="list">
                  <li v-for="(item, index) in goodsEvaluateList" :key="index">
                    <div class="member-info">
                      <img :src="$img(item.member_headimg)" @error="imageErrorEvaluate(index)" class="avatar" />
                      <span>{{ item.member_name }}</span>
                    </div>
                    <div class="info-wrap">
                      <el-rate v-model="item.star" disabled></el-rate>
                      <p class="content">{{ item.content }}</p>
                      <div class="img-list" v-if="item.images">
                        <el-image v-for="(img, img_index) in item.images" :key="img_index" :src="$img(img)" :preview-src-list="item.imagesFormat"/>
                      </div>
                      <div class="sku-info">
                        <span>{{ item.sku_name }}</span>
                        <span class="create-time">{{ $util.timeStampTurnTime(item.create_time) }}</span>
                      </div>
                      <div class="evaluation-reply" v-if="item.explain_first != ''">店家回复：{{ item.explain_first }}</div>
                      <template v-if="item.again_content != ''">
                        <div class="review-evaluation">
                          <span>追加评价</span>
                          <span class="review-time">{{ $util.timeStampTurnTime(item.again_time) }}</span>
                        </div>
                        <p class="content">{{ item.again_content }}</p>
                        <div class="img-list">
                          <el-image v-for="(again_img, again_index) in item.again_images" :key="again_index" :src="$img(again_img)" :preview-src-list="item.againImagesFormat"/>
                        </div>
                        <div class="evaluation-reply" v-if="item.again_explain != ''">店家回复：{{ item.again_explain }}
                        </div>
                      </template>
                    </div>
                  </li>
                </ul>
                <div class="pager">
                  <el-pagination background :pager-count="5" :total="total" prev-text="上一页" next-text="下一页"
                                 :current-page.sync="currentPage" :page-size.sync="pageSize"
                                 @size-change="handlePageSizeChange"
                                 @current-change="handleCurrentPageChange" hide-on-single-page></el-pagination>
                </div>
              </template>
              <div class="empty" v-else>该商品暂无评价哦</div>
            </el-tab-pane>
            <template v-if="service">
              <el-tab-pane v-if="service.is_display == 1" label="售后保障" name="after_sale" class="after-sale">
                <div v-html="service.content"></div>
              </el-tab-pane>
            </template>
          </el-tabs>
        </div>
        <!--联系客服弹窗-->
        <servicerMessage ref="servicerMessage" class="kefu" :shop="{shop_id:shopInfo.site_id,logo:shopInfo.logo,shop_name:shopInfo.site_name}"></servicerMessage>
      </div>
    </div>
  </div>
</template>

<script>
  import PicZoom from 'vue-piczoom';
  import detail from '@/assets/js/promotion/detail_groupbuy.js';
  import GoodsRecommend from '@/components/GoodsRecommend';
  import servicerMessage from "@/components/message/servicerMessage";

  export default {
    name: 'groupbuy_detail',
    components: {
      PicZoom,
      GoodsRecommend,
      servicerMessage
    },
    mixins: [detail]
  };
</script>
<style lang="scss">
  @import '@/assets/css/promotion/detail_groupbuy.scss';
</style>
