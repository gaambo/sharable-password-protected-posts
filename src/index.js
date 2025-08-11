import { registerPlugin } from "@wordpress/plugins";
import PostStatusSettings from "./PostStatusSettings";

registerPlugin("private-post-share", {
    render: PostStatusSettings,
});
