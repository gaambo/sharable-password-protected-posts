import { CheckboxControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useSelect, useDispatch } from "@wordpress/data";
import { store as editorStore } from "@wordpress/editor";
import CopyUrl from "./CopyUrl";
import Check from "./Check";

// Support < WP 6.6 and > WP 6.6
// See https://make.wordpress.org/core/2024/06/18/editor-unified-extensibility-apis-in-6-6/
const PluginPostStatusInfo = wp.editor?.PluginPostStatusInfo ?? ( wp.editPost?.PluginPostStatusInfo ?? wp.editSite?.PluginPostStatusInfo );

import CONSTANTS from "./constants";
const { META_ENABLED, META_KEY } = CONSTANTS;

import "./styles.scss";

const PostStatusSettings = () => {
    const { sharingEnabled, existingKey } = useSelect((select) => {
        const { getEditedPostAttribute } = select(editorStore);
        const meta = getEditedPostAttribute("meta"); // Will be undefined in site editor.
        return {
            sharingEnabled: meta?.[META_ENABLED] || null,
            existingKey: meta?.[META_KEY] || null,
        };
    });
    const { editPost } = useDispatch(editorStore);
    const onChangeEnabled = (value) => {
        if (value) {
            editPost({
                meta: { [META_ENABLED]: true, [META_KEY]: existingKey || window.sppp.newKey || "" },
            });
        } else {
            editPost({
                meta: { [META_ENABLED]: false, [META_KEY]: "" },
            });
        }
    };

    return (
        <PluginPostStatusInfo className="sppp">
            <Check>
                <div className="sppp__checkbox">
                    <CheckboxControl
                        label={__("Share post via secret URL", "sharable-password-protected-posts")}
                        checked={!!sharingEnabled} // Cast to bool, so null value after changing and reloading state does not trigger uncontrolled input warning.
                        onChange={onChangeEnabled}
                    />
                </div>

                {sharingEnabled && (
                    <div className="sppp__link">
                        <CopyUrl />
                    </div>
                )}
            </Check>
        </PluginPostStatusInfo>
    );
};

export default PostStatusSettings;
