import { PluginPostStatusInfo } from "@wordpress/edit-post";
import { PanelRow, CheckboxControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useSelect, useDispatch } from "@wordpress/data";
import { store as editorStore } from "@wordpress/editor";
import CopyUrl from "./CopyUrl";
import Check from "./Check";

import CONSTANTS from "./constants";
const { META_ENABLED, META_KEY } = CONSTANTS;

import "./styles.scss";

const PostStatusSettings = () => {
    const { sharingEnabled, existingKey } = useSelect((select) => {
        const { getEditedPostAttribute } = select(editorStore);
        const meta = getEditedPostAttribute("meta");
        return {
            sharingEnabled: meta[META_ENABLED],
            existingKey: meta[META_KEY],
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
                        label={__("Share post via secret URL", "sppp")}
                        checked={sharingEnabled}
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
