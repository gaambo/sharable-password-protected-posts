import { registerPlugin } from "@wordpress/plugins";
import PostStatusSettings from "./PostStatusSettings";

registerPlugin("sharable-password-protected-posts", {
	render: PostStatusSettings,
});
