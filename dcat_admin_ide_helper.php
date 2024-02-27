<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection is_enabled
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection code
     * @property Grid\Column|Collection encode
     * @property Grid\Column|Collection mode
     * @property Grid\Column|Collection decline_min
     * @property Grid\Column|Collection decline_max
     * @property Grid\Column|Collection start_time
     * @property Grid\Column|Collection end_time
     * @property Grid\Column|Collection status
     * @property Grid\Column|Collection uid
     * @property Grid\Column|Collection cid
     * @property Grid\Column|Collection mchid
     * @property Grid\Column|Collection signkey
     * @property Grid\Column|Collection appid
     * @property Grid\Column|Collection secret
     * @property Grid\Column|Collection qrcode
     * @property Grid\Column|Collection aptitude
     * @property Grid\Column|Collection time_limit
     * @property Grid\Column|Collection public_secret
     * @property Grid\Column|Collection private_secret
     * @property Grid\Column|Collection day_limit
     * @property Grid\Column|Collection min_amount
     * @property Grid\Column|Collection max_amount
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection sys_order
     * @property Grid\Column|Collection shop_order
     * @property Grid\Column|Collection amount
     * @property Grid\Column|Collection shop_amount
     * @property Grid\Column|Collection cost_amount
     * @property Grid\Column|Collection code_amount
     * @property Grid\Column|Collection source_url
     * @property Grid\Column|Collection notiry_url
     * @property Grid\Column|Collection callback_url
     * @property Grid\Column|Collection codename
     * @property Grid\Column|Collection client
     * @property Grid\Column|Collection client_ip
     * @property Grid\Column|Collection aid
     * @property Grid\Column|Collection aname
     * @property Grid\Column|Collection notify_at
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection expires_at
     * @property Grid\Column|Collection freeze_amount
     * @property Grid\Column|Collection api_ip
     * @property Grid\Column|Collection api_key
     * @property Grid\Column|Collection attr
     * @property Grid\Column|Collection deleted_at
     * @property Grid\Column|Collection rate
     * @property Grid\Column|Collection oid
     * @property Grid\Column|Collection befor_amount
     * @property Grid\Column|Collection after_amount
     * @property Grid\Column|Collection bank_name
     * @property Grid\Column|Collection branch_name
     * @property Grid\Column|Collection card_no
     * @property Grid\Column|Collection province
     * @property Grid\Column|Collection city
     * @property Grid\Column|Collection alias
     * @property Grid\Column|Collection cost
     * @property Grid\Column|Collection actual_name
     *
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection code(string $label = null)
     * @method Grid\Column|Collection encode(string $label = null)
     * @method Grid\Column|Collection mode(string $label = null)
     * @method Grid\Column|Collection decline_min(string $label = null)
     * @method Grid\Column|Collection decline_max(string $label = null)
     * @method Grid\Column|Collection start_time(string $label = null)
     * @method Grid\Column|Collection end_time(string $label = null)
     * @method Grid\Column|Collection status(string $label = null)
     * @method Grid\Column|Collection uid(string $label = null)
     * @method Grid\Column|Collection cid(string $label = null)
     * @method Grid\Column|Collection mchid(string $label = null)
     * @method Grid\Column|Collection signkey(string $label = null)
     * @method Grid\Column|Collection appid(string $label = null)
     * @method Grid\Column|Collection secret(string $label = null)
     * @method Grid\Column|Collection qrcode(string $label = null)
     * @method Grid\Column|Collection aptitude(string $label = null)
     * @method Grid\Column|Collection time_limit(string $label = null)
     * @method Grid\Column|Collection public_secret(string $label = null)
     * @method Grid\Column|Collection private_secret(string $label = null)
     * @method Grid\Column|Collection day_limit(string $label = null)
     * @method Grid\Column|Collection min_amount(string $label = null)
     * @method Grid\Column|Collection max_amount(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection sys_order(string $label = null)
     * @method Grid\Column|Collection shop_order(string $label = null)
     * @method Grid\Column|Collection amount(string $label = null)
     * @method Grid\Column|Collection shop_amount(string $label = null)
     * @method Grid\Column|Collection cost_amount(string $label = null)
     * @method Grid\Column|Collection code_amount(string $label = null)
     * @method Grid\Column|Collection source_url(string $label = null)
     * @method Grid\Column|Collection notiry_url(string $label = null)
     * @method Grid\Column|Collection callback_url(string $label = null)
     * @method Grid\Column|Collection codename(string $label = null)
     * @method Grid\Column|Collection client(string $label = null)
     * @method Grid\Column|Collection client_ip(string $label = null)
     * @method Grid\Column|Collection aid(string $label = null)
     * @method Grid\Column|Collection aname(string $label = null)
     * @method Grid\Column|Collection notify_at(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection expires_at(string $label = null)
     * @method Grid\Column|Collection freeze_amount(string $label = null)
     * @method Grid\Column|Collection api_ip(string $label = null)
     * @method Grid\Column|Collection api_key(string $label = null)
     * @method Grid\Column|Collection attr(string $label = null)
     * @method Grid\Column|Collection deleted_at(string $label = null)
     * @method Grid\Column|Collection rate(string $label = null)
     * @method Grid\Column|Collection oid(string $label = null)
     * @method Grid\Column|Collection befor_amount(string $label = null)
     * @method Grid\Column|Collection after_amount(string $label = null)
     * @method Grid\Column|Collection bank_name(string $label = null)
     * @method Grid\Column|Collection branch_name(string $label = null)
     * @method Grid\Column|Collection card_no(string $label = null)
     * @method Grid\Column|Collection province(string $label = null)
     * @method Grid\Column|Collection city(string $label = null)
     * @method Grid\Column|Collection alias(string $label = null)
     * @method Grid\Column|Collection cost(string $label = null)
     * @method Grid\Column|Collection actual_name(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection id
     * @property Show\Field|Collection name
     * @property Show\Field|Collection type
     * @property Show\Field|Collection version
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection is_enabled
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection value
     * @property Show\Field|Collection username
     * @property Show\Field|Collection password
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection code
     * @property Show\Field|Collection encode
     * @property Show\Field|Collection mode
     * @property Show\Field|Collection decline_min
     * @property Show\Field|Collection decline_max
     * @property Show\Field|Collection start_time
     * @property Show\Field|Collection end_time
     * @property Show\Field|Collection status
     * @property Show\Field|Collection uid
     * @property Show\Field|Collection cid
     * @property Show\Field|Collection mchid
     * @property Show\Field|Collection signkey
     * @property Show\Field|Collection appid
     * @property Show\Field|Collection secret
     * @property Show\Field|Collection qrcode
     * @property Show\Field|Collection aptitude
     * @property Show\Field|Collection time_limit
     * @property Show\Field|Collection public_secret
     * @property Show\Field|Collection private_secret
     * @property Show\Field|Collection day_limit
     * @property Show\Field|Collection min_amount
     * @property Show\Field|Collection max_amount
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection sys_order
     * @property Show\Field|Collection shop_order
     * @property Show\Field|Collection amount
     * @property Show\Field|Collection shop_amount
     * @property Show\Field|Collection cost_amount
     * @property Show\Field|Collection code_amount
     * @property Show\Field|Collection source_url
     * @property Show\Field|Collection notiry_url
     * @property Show\Field|Collection callback_url
     * @property Show\Field|Collection codename
     * @property Show\Field|Collection client
     * @property Show\Field|Collection client_ip
     * @property Show\Field|Collection aid
     * @property Show\Field|Collection aname
     * @property Show\Field|Collection notify_at
     * @property Show\Field|Collection email
     * @property Show\Field|Collection token
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection expires_at
     * @property Show\Field|Collection freeze_amount
     * @property Show\Field|Collection api_ip
     * @property Show\Field|Collection api_key
     * @property Show\Field|Collection attr
     * @property Show\Field|Collection deleted_at
     * @property Show\Field|Collection rate
     * @property Show\Field|Collection oid
     * @property Show\Field|Collection befor_amount
     * @property Show\Field|Collection after_amount
     * @property Show\Field|Collection bank_name
     * @property Show\Field|Collection branch_name
     * @property Show\Field|Collection card_no
     * @property Show\Field|Collection province
     * @property Show\Field|Collection city
     * @property Show\Field|Collection alias
     * @property Show\Field|Collection cost
     * @property Show\Field|Collection actual_name
     *
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection code(string $label = null)
     * @method Show\Field|Collection encode(string $label = null)
     * @method Show\Field|Collection mode(string $label = null)
     * @method Show\Field|Collection decline_min(string $label = null)
     * @method Show\Field|Collection decline_max(string $label = null)
     * @method Show\Field|Collection start_time(string $label = null)
     * @method Show\Field|Collection end_time(string $label = null)
     * @method Show\Field|Collection status(string $label = null)
     * @method Show\Field|Collection uid(string $label = null)
     * @method Show\Field|Collection cid(string $label = null)
     * @method Show\Field|Collection mchid(string $label = null)
     * @method Show\Field|Collection signkey(string $label = null)
     * @method Show\Field|Collection appid(string $label = null)
     * @method Show\Field|Collection secret(string $label = null)
     * @method Show\Field|Collection qrcode(string $label = null)
     * @method Show\Field|Collection aptitude(string $label = null)
     * @method Show\Field|Collection time_limit(string $label = null)
     * @method Show\Field|Collection public_secret(string $label = null)
     * @method Show\Field|Collection private_secret(string $label = null)
     * @method Show\Field|Collection day_limit(string $label = null)
     * @method Show\Field|Collection min_amount(string $label = null)
     * @method Show\Field|Collection max_amount(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection sys_order(string $label = null)
     * @method Show\Field|Collection shop_order(string $label = null)
     * @method Show\Field|Collection amount(string $label = null)
     * @method Show\Field|Collection shop_amount(string $label = null)
     * @method Show\Field|Collection cost_amount(string $label = null)
     * @method Show\Field|Collection code_amount(string $label = null)
     * @method Show\Field|Collection source_url(string $label = null)
     * @method Show\Field|Collection notiry_url(string $label = null)
     * @method Show\Field|Collection callback_url(string $label = null)
     * @method Show\Field|Collection codename(string $label = null)
     * @method Show\Field|Collection client(string $label = null)
     * @method Show\Field|Collection client_ip(string $label = null)
     * @method Show\Field|Collection aid(string $label = null)
     * @method Show\Field|Collection aname(string $label = null)
     * @method Show\Field|Collection notify_at(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection expires_at(string $label = null)
     * @method Show\Field|Collection freeze_amount(string $label = null)
     * @method Show\Field|Collection api_ip(string $label = null)
     * @method Show\Field|Collection api_key(string $label = null)
     * @method Show\Field|Collection attr(string $label = null)
     * @method Show\Field|Collection deleted_at(string $label = null)
     * @method Show\Field|Collection rate(string $label = null)
     * @method Show\Field|Collection oid(string $label = null)
     * @method Show\Field|Collection befor_amount(string $label = null)
     * @method Show\Field|Collection after_amount(string $label = null)
     * @method Show\Field|Collection bank_name(string $label = null)
     * @method Show\Field|Collection branch_name(string $label = null)
     * @method Show\Field|Collection card_no(string $label = null)
     * @method Show\Field|Collection province(string $label = null)
     * @method Show\Field|Collection city(string $label = null)
     * @method Show\Field|Collection alias(string $label = null)
     * @method Show\Field|Collection cost(string $label = null)
     * @method Show\Field|Collection actual_name(string $label = null)
     */
    class Show {}

    /**
     
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
