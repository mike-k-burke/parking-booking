
# Parking Booking API

This is a simple API for a single parking garage.

It provides the manager of the garage the ability to:
* view the details for a day in the calendar, including the number of bookings made
* manage the number of available parking spaces for each day in the calendar
* manage the price for a customer to book a parking space for each day in the calendar

It provides customers the ability to:
* check the availability and total price to book for a given date range
* create a booking for a given date range
* view the details of a booking
* edit the date range or vehicle registration on a booking
* cancel a booking
* check and amend their stored contact details

For simplicity's sake, this API assumes all bookings will be for full day periods. A future extension could break down the bookable period to an hourly, rather than daily, basis.

I have also realised as I write the documentation that I have assumed that cars can be moved between parking spaces as required for space/availibility requirements, as they would in a valet parking garage. Some sort of entity to represent a single parking space and a refactor of the availability checking system would be required for a parking garage where the customer controls where they park.



## API Reference

#### Get calendar day

```http
  GET /api/calendar/${date}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `date`    | `date:Y-m-d` | **Required**. Date of calendar day to fetch |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `date` | `date:Y-m-d` | Date of calendar day   |
| `available_spaces` | `integer` | Number of total available spaces for the calendar day   |
| `booked_spaces` | `integer` | Number of booked spaces for the calendar day   |
| `has_free_spaces` | `boolean` | Is the calendar day free to have a new booking added?   |
| `price`    | `integer` | Price to book a parking space for the calendar day in pence  |

#### Get calendar day range

```http
  GET /api/calendar/${start}/${end}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `start`    | `date:Y-m-d` | **Required**. Start date of calendar day range to fetch |
| `end`    | `date:Y-m-d` | **Required**. End date of calendar day range to fetch |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `data` | `array` | Array of calendar days in selected range   |
| `data[][date]` | `date:Y-m-d` | Date of calendar day   |
| `data[][available_spaces]` | `integer` | Number of total available spaces for the calendar day   |
| `data[][booked_spaces]` | `integer` | Number of booked spaces for the calendar day   |
| `data[][has_free_spaces]` | `boolean` | Is the calendar day free to have a new booking added?   |
| `data[][price]`    | `integer` | Price to book a parking space for the calendar day in pence  |
| `meta` | `array` | Array of data referencing the range as a whole   |
| `meta[][start]` | `date:Y-m-d` | Date of the first calendar day in the range  |
| `meta[][end]` | `date:Y-m-d` | Date of the last calendar day in the range  |
| `meta[][is_available]` | `boolean` | Is there a free parking space to book for the whole range?  |
| `meta[][price]` | `integer` | Price to book a parking space for the whole range of calendar days in pence |


#### Update prices for calendar days

```http
  POST /api/calendar/price
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `price`   | `integer` | **Required**. Price to update days to in pence, minimum 0 |
| `start`   | `date:Y-m-d` | **Required**. Start date of calendar day range to update |
| `end`    | `date:Y-m-d` | **Required**. End date of calendar day range to update |
| `exclude_weekends` | `boolean` | Exclude weekend days from the update? |
| `exclude_weekdays` | `boolean` | Exclude weekday days from the update? |
| `exclude_days` | `array[date:Y-m-d]` | Array of any explicit dates to exclude from the update |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `success` | `boolean` | Was update successful?   |

#### Update available spaces for calendar days

```http
  POST /api/calendar/spaces
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `available_spaces` | `integer` | **Required**. Number of available spaces to update days to, minimum 0 |
| `start`   | `date:Y-m-d` | **Required**. Start date of calendar day range to update |
| `end`    | `date:Y-m-d` | **Required**. End date of calendar day range to update |
| `exclude_weekends` | `boolean` | Exclude weekend days from the update? |
| `exclude_weekdays` | `boolean` | Exclude weekday days from the update? |
| `exclude_days` | `array[date:Y-m-d]` | Array of any explicit dates to exclude from the update |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `success` | `boolean` | Was update successful?   |

#### Create booking

```http
  POST /api/bookings
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `registration` | `string` | **Required**. Registration of vehicle for the booking |
| `start`   | `date:Y-m-d` | **Required**. Start date of booking |
| `end`    | `date:Y-m-d` | **Required**. End date of booking |
| `email` | `string` | **Required**. Email address of the customer |
| `mobile` | `string` | Optional contact number of the customer |
| `password` | `string` | Optional password to register the customer as a user |
| `password_confirmation` | `string` | If a password is passed, must be present and ientical to the password |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `id` | `integer` | Id of booking   |
| `registration` | `string` | Registration of vehicle for the booking   |
| `customer`      | `array` | Booking customer details |
| `customer[id]`      | `integer` | Id of customer |
| `customer[email]` | `string` | Email address of the customer |
| `customer[mobile]` | `string` | Contact number of the customer |
| `start` | `date:Y-m-d` | Start date of booking |
| `end` | `date:Y-m-d` | End date of booking |
| `price` | `integer` | Price of booking in pence  |
| `created_at`  | `timestamp` | Date/time when booking created  |
| `updated_at` | `timestamp` | Date/time when booking last updated |
| `booking_days` | `array` | Array of the days making up the booking |
| `booking_days[][date]` | `date:Y-m-d` | Date of the booking day |
| `booking_days[][price]` | `integer` | Price of the booking day |
| `booking_days[][created_at]`  | `timestamp` | Date/time when booking day created  |
| `booking_days[][updated_at]` | `timestamp` | Date/time when booking day last updated |

#### View booking details

```http
  GET /api/bookings/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id`      | `integer` | **Required**. Id of booking |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `id` | `integer` | Id of booking   |
| `registration` | `string` | Registration of vehicle for the booking   |
| `customer`      | `array` | Booking customer details |
| `customer[id]`      | `integer` | Id of customer |
| `customer[email]` | `string` | Email address of the customer |
| `customer[mobile]` | `string` | Contact number of the customer |
| `start` | `date:Y-m-d` | Start date of booking |
| `end` | `date:Y-m-d` | End date of booking |
| `price` | `integer` | Price of booking in pence  |
| `created_at`  | `timestamp` | Date/time when booking created  |
| `updated_at` | `timestamp` | Date/time when booking last updated |
| `booking_days` | `array` | Array of the days making up the booking |
| `booking_days[][date]` | `date:Y-m-d` | Date of the booking day |
| `booking_days[][price]` | `integer` | Price of the booking day |
| `booking_days[][created_at]`  | `timestamp` | Date/time when booking day created  |
| `booking_days[][updated_at]` | `timestamp` | Date/time when booking day last updated |

#### Amend booking details

```http
  PUT /api/bookings/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id`      | `integer` | **Required**. Id of booking |
| `registration` | `string` | Optional new registration of vehicle for the booking |
| `start`   | `date:Y-m-d` | Optional new start date of booking, if provided, end date must also be provided |
| `end`    | `date:Y-m-d` | Optional new end date of booking, if provided, start date must also be provided |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `id` | `integer` | Id of booking   |
| `registration` | `string` | Registration of vehicle for the booking   |
| `customer`      | `array` | Booking customer details |
| `customer[id]`      | `integer` | Id of customer |
| `customer[email]` | `string` | Email address of the customer |
| `customer[mobile]` | `string` | Contact number of the customer |
| `start` | `date:Y-m-d` | Start date of booking |
| `end` | `date:Y-m-d` | End date of booking |
| `price` | `integer` | Price of booking in pence  |
| `created_at`  | `timestamp` | Date/time when booking created  |
| `updated_at` | `timestamp` | Date/time when booking last updated |
| `booking_days` | `array` | Array of the days making up the booking |
| `booking_days[][date]` | `date:Y-m-d` | Date of the booking day |
| `booking_days[][price]` | `integer` | Price of the booking day |
| `booking_days[][created_at]`  | `timestamp` | Date/time when booking day created  |
| `booking_days[][updated_at]` | `timestamp` | Date/time when booking day last updated |

#### Cancel booking

```http
  DELETE /api/bookings/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id`      | `integer` | **Required**. Id of booking |

#### View customer details

```http
  GET /api/customers/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id`      | `integer` | **Required**. Id of customer |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `id`      | `integer` | Id of customer |
| `email` | `string` | Email address of the customer |
| `mobile` | `string` | Contact number of the customer |

#### Amend customer details

```http
  PUT /api/customers/${id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `id`      | `integer` | **Required**. Id of customer |
| `email` | `string` | Optional new email address of the customer |
| `mobile` | `string` | Optional new contact number of the customer |
| `password` | `string` | Optional new password of the customer |

| Response Field | Type     | Description           |
| :------------- | :------- | :-------------------- |
| `id`      | `integer` | Id of customer |
| `email` | `string` | Email address of the customer |
| `mobile` | `string` | Contact number of the customer |
