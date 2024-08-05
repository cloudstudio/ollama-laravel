<?php

namespace Cloudstudio\Ollama\Traits;

use Psr\Http\Message\StreamInterface;

trait StreamHelper {
    protected static function doProcessStream(StreamInterface $body, \Closure $handleJsonObject): array {
        // Use a buffer to hold incomplete JSON object parts
        $buffer = '';

        $jsonObjects = [];

        while (!$body->eof()) {
            $chunk = $body->read(256);
            $buffer .= $chunk;

            // Split the buffer by newline as a delimiter
            while (($pos = strpos($buffer, "\n")) !== false) {
                $json = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                // Attempt to decode the JSON object
                $data = json_decode($json, true);

                // Check if JSON decoding was successful
                // if so, pass the object to the handler
                if ($data !== null) {
                    $handleJsonObject($data);
                    $jsonObjects[] = $data;
                } else {
                    // If JSON decoding fails, it means this is an incomplete object,
                    // So, we append this part back to the buffer to be processed with the next chunk
                    $buffer = $json . "\n" . $buffer;
                    break;
                }
            }
        }

        // Process any remaining data in the buffer
        if (!empty($buffer)) {
            $data = json_decode($buffer, true);
            if ($data !== null) {
                $handleJsonObject($data);
                $jsonObjects[] = $data;
            } else {
                // we shouldn't ever hit this, except maybe when the ollama docker container is unexpectedly killed
                throw new \Exception( "Incomplete JSON object remaining: " . $buffer);
            }
        }

        return $jsonObjects;
    }
}
