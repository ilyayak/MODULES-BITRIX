<?php


namespace Only\Rest\Client\Loyalty;


use Exception;

class Orders extends Main
{
    /**
     * @param array $arFilter ['customer' => '8596']
     * @return array
     * @throws Exception
     *
     * {
     * "count": 3,
     * "next": null,
     * "previous": null,
     * "results": [
     * {
     * "id": 5863,
     * "point_of_sale_id": 1,
     * "parent_id": null,
     * "customer": {
     * "id": 8596,
     * "url": "http://bonus.olgaberloga.ru:2015/api/customers/8596/",
     * "external_id": "9f80d2f5-bc8c-4120-af3f-477a8ce9883c",
     * "is_verified": false,
     * "first_name": "Андрей",
     * "last_name": "Уфимцев",
     * "middle_name": "Анатольевич",
     * "birth_date": "1965-11-10",
     * "gender": "M",
     * "language": null,
     * "phone": "+79516005235",
     * "phone_verified": false,
     * "allow_phone_contact": false,
     * "email": null,
     * "allow_email_contact": false,
     * "notes": null,
     * "date_created": "2020-09-15T06:59:46.461941+03:00",
     * "date_modified": "2020-10-06T14:58:40.892728+03:00",
     * "ext_date_created": "2018-08-21T13:16:51.223000+03:00",
     * "ext_date_modified": "2020-09-15T10:37:10.887000+03:00",
     * "is_closed": false,
     * "merged_to": null
     * },
     * "customer_id": 8596,
     * "customer_tier": null,
     * "customer_tier_id": null,
     * "card": null,
     * "promo_code": null,
     * "name": "Уфимцев Андрей Анатольевич",
     * "notes": "работают на белке и берлоге. Заселились 14.06.20",
     * "type": "",
     * "date_start": "2020-09-14T14:00:00+03:00",
     * "date_end": "2020-09-18T00:00:00+03:00",
     * "external_id": "170142",
     * "items": [
     * {
     * "id": 23336,
     * "order_id": 5863,
     * "external_id": "233683",
     * "date": "2020-09-17T12:00:00+03:00",
     * "group_id": "",
     * "revenue_type": "ROOM",
     * "code": "200",
     * "name": "Проживание",
     * "sub_code": null,
     * "amount": "1500.00000",
     * "amount_before_discount": "1500.00000",
     * "included_tax_amount": "0.00000",
     * "quantity": "1.00000",
     * "is_scheduled": false,
     * "date_created": "2020-09-15T06:37:24.855628+03:00",
     * "date_modified": "2020-09-18T05:53:54.486779+03:00",
     * "ext_date_created": "2020-09-15T10:37:10.120000+03:00",
     * "ext_date_modified": null
     * },
     * {
     * "id": 23335,
     * "order_id": 5863,
     * "external_id": "233682",
     * "date": "2020-09-16T12:00:00+03:00",
     * "group_id": "",
     * "revenue_type": "ROOM",
     * "code": "200",
     * "name": "Проживание",
     * "sub_code": null,
     * "amount": "1500.00000",
     * "amount_before_discount": "1500.00000",
     * "included_tax_amount": "0.00000",
     * "quantity": "1.00000",
     * "is_scheduled": false,
     * "date_created": "2020-09-15T06:37:24.855411+03:00",
     * "date_modified": "2020-09-18T05:53:54.497875+03:00",
     * "ext_date_created": "2020-09-15T10:37:10.120000+03:00",
     * "ext_date_modified": null
     * },
     * {
     * "id": 23334,
     * "order_id": 5863,
     * "external_id": "233681",
     * "date": "2020-09-15T12:00:00+03:00",
     * "group_id": "",
     * "revenue_type": "ROOM",
     * "code": "200",
     * "name": "Проживание",
     * "sub_code": null,
     * "amount": "1500.00000",
     * "amount_before_discount": "1500.00000",
     * "included_tax_amount": "0.00000",
     * "quantity": "1.00000",
     * "is_scheduled": false,
     * "date_created": "2020-09-15T06:37:24.855188+03:00",
     * "date_modified": "2020-09-18T05:53:54.517226+03:00",
     * "ext_date_created": "2020-09-15T10:37:10.120000+03:00",
     * "ext_date_modified": null
     * },
     * {
     * "id": 23333,
     * "order_id": 5863,
     * "external_id": "233680",
     * "date": "2020-09-14T14:00:00+03:00",
     * "group_id": "",
     * "revenue_type": "ROOM",
     * "code": "200",
     * "name": "Проживание",
     * "sub_code": null,
     * "amount": "1500.00000",
     * "amount_before_discount": "1500.00000",
     * "included_tax_amount": "0.00000",
     * "quantity": "1.00000",
     * "is_scheduled": false,
     * "date_created": "2020-09-15T06:37:24.854846+03:00",
     * "date_modified": "2020-09-18T05:53:54.538420+03:00",
     * "ext_date_created": "2020-09-15T10:37:10.120000+03:00",
     * "ext_date_modified": null
     * }
     * ],
     * "payments": [
     * {
     * "id": 8432,
     * "order_id": 5863,
     * "external_id": "LGP-edc74227-6b38-4952-be02-42cb1e291f50",
     * "date": "2020-09-18T00:00:00+03:00",
     * "code": "150",
     * "name": "Безнал (310058-ООО \"ОК \"СШС\")",
     * "type": "BANK",
     * "amount": "6000.00000",
     * "group_id": "",
     * "date_created": "2020-09-18T05:53:47.137091+03:00",
     * "date_modified": "2020-09-18T05:53:54.476486+03:00",
     * "ext_date_created": "2020-09-18T05:53:27.966250+03:00",
     * "ext_date_modified": null
     * }
     * ],
     * "operations": [],
     * "status": "COMPL",
     * "extra_fields": [
     * {
     * "id": 85618,
     * "name": "Layout.ExtraBed",
     * "value": "0"
     * },
     * {
     * "id": 85617,
     * "name": "Layout.Child5",
     * "value": "0"
     * },
     * {
     * "id": 85616,
     * "name": "Layout.Child4",
     * "value": "0"
     * },
     * {
     * "id": 85615,
     * "name": "Layout.Child3",
     * "value": "0"
     * },
     * {
     * "id": 85614,
     * "name": "Layout.Child2",
     * "value": "0"
     * },
     * {
     * "id": 85613,
     * "name": "Layout.Child1",
     * "value": "0"
     * },
     * {
     * "id": 85612,
     * "name": "Layout.Adults",
     * "value": "3"
     * },
     * {
     * "id": 85611,
     * "name": "CheckedOutUser.Name",
     * "value": "Вершинина Татьяна Васильевна"
     * },
     * {
     * "id": 85610,
     * "name": "CheckedInUser.Name",
     * "value": "Зеленин Дмитрий Викторович"
     * },
     * {
     * "id": 85609,
     * "name": "CreatedUser.Name",
     * "value": "Зеленин Дмитрий Викторович"
     * },
     * {
     * "id": 85608,
     * "name": "Room.Code",
     * "value": "106"
     * },
     * {
     * "id": 85607,
     * "name": "RoomType.Name",
     * "value": "Стандарт семейный (3-местный) с тремя односпальными кроватями"
     * },
     * {
     * "id": 85606,
     * "name": "RoomType.Code",
     * "value": "СТД1+1+1"
     * },
     * {
     * "id": 85604,
     * "name": "Rate.Code",
     * "value": "РАБСОПЛ"
     * }
     * ],
     * "url": "http://bonus.olgaberloga.ru:2015/api/orders/5863/",
     * "market_source": "PHNE",
     * "market_segment": "ОСН",
     * "market_geo_code": "КЕМЕР",
     * "market_track_code": "INT",
     * "market_open_code": "BUS",
     * "market_extra_1": null,
     * "market_extra_2": null,
     * "date_created": "2020-09-15T06:37:24.832473+03:00",
     * "date_modified": "2020-09-18T05:53:54.457645+03:00",
     * "ext_date_created": "2020-09-15T10:37:10.120000+03:00",
     * "ext_date_modified": "2020-09-18T05:53:35.684965+03:00"
     * }]]
     */
    public function getList(array $arFilter)
    {
        $query = http_build_query($arFilter);

        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/orders/?{$query}";

        return $this->query();
    }

    public function getById($id)
    {
        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/orders/{$id}/";

        return $this->query();
    }
}
