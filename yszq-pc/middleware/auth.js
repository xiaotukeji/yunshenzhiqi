import { getToken } from "~/utils/auth"

export default function (context) {
  if (process.client && !getToken()) {
    context.redirect(302, {path: `/auth/login?redirect=${encodeURIComponent(context.route.fullPath)}`})
    return;
  }
}
