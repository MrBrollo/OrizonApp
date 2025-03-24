
# Orizon 

An App that will help you plan your travels more efficiently.


## Run Locally

Clone the project

```
git clone https://github.com/MrBrollo/OrizonApp.git
```


## Running Tests

You can run tests with the following commands

Add a country:
```
curl -X POST http://localhost/paesi.php -d '{"name": "Italia"}' -H "Content-Type: application/json"
```

Get all countries:
```
curl -X GET http://localhost/paesi.php
```

Modify a country:
```
curl -X PUT http://localhost/paesi.php -d '{"id": 1, "name": "Francia"}' -H "Content-Type: application/json"
```

Delete a country:
```
curl -X DELETE http://localhost/paesi.php -d '{"id": 1}' -H "Content-Type: application/json"
```
Get all travels
```
curl -X GET http://localhost/viaggi.php
```
Sort by minimun number of seats available (i.e. 10 seats)
```
curl -X GET "http://localhost/viaggi.php?min_seats=10"
```
## Authors

- [@MrBrollo](https://github.com/MrBrollo)
