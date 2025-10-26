// plugins/route.js文件：
export default ({
  app,
  redirect
}) => {
  app.router.afterEach((to, from) => {
    window.scrollTo(0, 0);
  })
}