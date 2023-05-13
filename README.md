# PHP-Authorization-with-JWT

Assume that you have multiple APIs and need a way of granting access securely to users. JSON Web Tokens are mainly used for this. You probably have seen an http request such as the one below:
```
GET /url HTTP/1.1
Host: yourhost.com
Connection: keep-alive
Accept: */*
X-Requested-With: XMLHttpRequest
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0MjU1ODg4MjEsImp0aSI6IjU0ZjhjMjU1NWQyMjMiLCJpc3MiOiJzcC1qd3Qtc2ltcGxlLXRlY25vbTFrMy5jOS5pbyIsIm5iZiI6MTQyNTU4ODgyMSwiZXhwIjoxNDI1NTkyNDIxLCJkYXRhIjp7InVzZXJJZCI6IjEiLCJ1c2VyTmFtZSI6ImFkbWluIn19.HVYBe9xvPD8qt0wh7rXI8bmRJsQavJ8Qs29yfVbY-A0
```

The Authorization: Bearer holds a JSON Web Token and this is what we will be generating and validating below.

### Generating a token
Before making the initial request to any API, we need to generate a new token first. We can do so by making a post request to 'http://localhost/api/auth/jwttoken' passing the claim set. The API KEY in this case will be used as the key for encrypting and decrypting the token.
```
curl -X POST http://localhost/api/auth/jwttoken -H 'Content-Type: application/json' -H 'APIKEY: ed47d3d45bd9e52b7fdb06f7a94bbe7e' -d '{"payload":{"iss":"yungcet","aud":"http://localhost/api/auth/OAuth2","iat":"1683889864", "exp":"1683893464"}}'
```
Results
```
{"message":"success","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5dW5nY2V0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdC9hcGkvYXV0aC9PQXV0aDIiLCJpYXQiOiIxNjgzODg5ODY0IiwiZXhwcCI6IjE2ODM4OTM0NjQifQ.2VO69vJ3AbjizZieUkx-OJui0PRssJzXOUNxAz2ToG8"}
```

### Creating a Claim Set
```
// encoded to a json string
$apikey = 'ed47d3d45bd9e52b7fdb06f7a94bbe7e';
$payload = json_encode (['payload' => [
    'iss' => 'yungcet',
    'aud' => 'http://localhost/api/auth/OAuth2',
    'iat' => time(),
    'exp' => time() + 3600 // expire in an hour
]]);

// then you can pass $payload to a request
curl -X POST https://localhost/api/auth/jwttoken -H 'Content-Type: application/json' -H 'APIKEY: ed47d3d45bd9e52b7fdb06f7a94bbe7e' -d '$payload'

// this is equivalent to
curl -X POST http://localhost/api/auth/jwttoken -H 'Content-Type: application/json' -H 'APIKEY: ed47d3d45bd9e52b7fdb06f7a94bbe7e' -d '{"payload":{"iss":"yungcet","aud":"[http://localhost/api/auth/OAuth2](http://localhost/api/auth/OAuth2)","iat":"1683881873", "exp":"1683885473"}}'
```

### Registered Claim Set
iss (issuer): Issuer of the JWT<br/>
sub (subject): Subject of the JWT (the user)<br/>
aud (audience): Recipient for which the JWT is intended<br/>
exp (expiration time): Time after which the JWT expires<br/>
nbf (not before time): Time before which the JWT must not be accepted for processing<br/>
iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT<br/>
jti (JWT ID): Unique identifier; can be used to prevent the JWT from being replayed (allows a token to be used only once)

### Authorization / validation of a token
Once we have the token we can post it to 'http://localhost/api/auth/OAuth2' to validate it. If the token is valid, a new token is returned from this call. From here on, whenever we're making a request we will be posting the token to 'http://localhost/api/auth/OAuth2' for validation before proceeding with the request.
```
curl -X POST http://localhost/api/auth/OAuth2 -H 'Content-Type: application/json' -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5dW5nY2V0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdC9hcGkvYXV0aC9PQXV0aDIiLCJpYXQiOiIxNjgzODg5ODY0IiwiZXhwcCI6IjE2ODM4OTM0NjQifQ.2VO69vJ3AbjizZieUkx-OJui0PRssJzXOUNxAz2ToG8' -H 'APIKEY: ed47d3d45bd9e52b7fdb06f7a94bbe7e'
{"error":"Invalid claim set","code":401}
```
Results
```
{"success":1,"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5dW5nY2V0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdC9hcGkvYXV0aC9PQXV0aDIiLCJpYXQiOjE2ODM4OTA4NTIsImV4cCI6MTY4Mzg5NDQ1Mn0.XeYJHqC_9v_9JlAnCJajAMA5RUpHp9nQE6NqIMENdbo"}
```
