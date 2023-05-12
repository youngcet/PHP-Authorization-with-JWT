<?php

    namespace App\Custom\API\Auth;

    require_once('vendor/autoload.php');

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\SignatureInvalidException;
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use DomainException;
    use InvalidArgumentException;
    use UnexpectedValueException;

    class OAuth2 
    {
        public static function isTokenValid ($now, $iss)
        {
            if (! preg_match('/Bearer\s(\S+)/', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
                return ["error" => 'Token not found', 'code' => 401];
            }

            $jwt = $matches[1];
            if (! $jwt) {
                return ["error" => 'Token not found set', 'code' => 401];
            }

            $decoded = self::decodeToken ($jwt, $_SERVER['HTTP_APIKEY'], 'HS256');
            
            $decodedtoarray = json_decode (json_encode ($decoded), true);
            if (isset ($decodedtoarray['error'])) return $decodedtoarray;

            foreach (['iss', 'iat', 'exp', 'aud'] as $item)
            {
                if (! isset ($decodedtoarray[$item])) return ["error" => 'Invalid claim set', 'code' => 401];
            }
            
            if ($decoded->iss !== $iss || $decoded->aud !== 'http://localhost/api/auth/OAuth2' || $decoded->exp < $now->getTimestamp())
            {
                return ["error" => 'Unauthorized ', 'code' => 401];
            }

            $decoded->iat = time();
            $decoded->exp = time() + 3600;

            return ['success' => 1, 'token' => self::generateToken (json_decode (json_encode ($decoded), true), $_SERVER['HTTP_APIKEY'], 'HS256')];
        }

        public static function generateToken ($payload, $key, $encryption)
        {
            return JWT::encode ($payload, $key, $encryption);
        }

        private static function decodeToken ($jwt, $key, $encryption)
        {
            try 
            {
                $decoded = JWT::decode ($jwt, new Key ($key, $encryption));
            } 
            catch (InvalidArgumentException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            } 
            catch (DomainException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            } 
            catch (SignatureInvalidException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            } 
            catch (BeforeValidException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            } 
            catch (ExpiredException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            } 
            catch (UnexpectedValueException $e) 
            {
                $decoded = json_encode (['error' => $e->getMessage(), 'code' => 401]);
            }
            
            return $decoded;
        } 
    }

?>