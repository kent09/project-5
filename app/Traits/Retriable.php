<?php

namespace App\Traits;

trait Retriable
{

    /**
     *
     * This will handle all the failed ids
     * from the bulkAssignTag method
     *
     * @param array $result
     * @param integer $tagId
     * @param string $method
     *
     * @return void
     */
    public function failedIds($result, $tagId, $method = '')
    {
        $con_service = $this->infusionsoftService->tags();
        $con_service->offsetSet("id", $tagId);

        $maxed_attempts = 3;
        $attempts = 1;

        do {
            $failed_ids = $this->mapFailedIds($result);

            if (count($failed_ids) > 0) {
                $new_result = call_user_func_array(array($con_service, $method), array($failed_ids));
                
                \Log::info('Re-attempting...' . print_r($attempts, true));
                
                if ($attempts === 3) {
                    \Log::info('Failed to retry for the 3rd time.');
                } else {
                    $failed_ids = $this->mapFailedIds($new_result);
                    \Log::info('New Failed Ids has been generated.');
                }
                
                $attempts++;
                sleep(1);
                continue;
            } else {
                $attempts = 3;
                break;
            }
        } while ($attempts <= $maxed_attempts);
    }

    /**
     *
     * This will map the failed ids
     *
     * @param array $result
     * @param string $re_attempt_status
     *
     * @return array
     */
    public function mapFailedIds($result, $re_attempt_status = 'FAILURE')
    {
        $failed_ids = [];
        if (count((array) $result) > 0) {
            foreach ($result as $r => $value) {
                if ($value === $re_attempt_status) {
                    $failed_ids[] = $r;
                }
            }
        }
        return $failed_ids;
    }


    /**
     *
     * This will handle the Different Exceptions
     *
     * @param array | [class, method] | $function
     * @param array | $params
     * @param array | $redirect ['redirect', 'error']
     *
     * @return string or void
     */
    public function handler($function, $params = [], $redirect = [])
    {
        $has_returned_value = false;
            
        $attempts = 1;
        $number_of_attempts = 3;
        $timer = 1;

        do {
            try {
                if (is_callable($function)) {
                    $has_returned_value = call_user_func_array($function, $params);
                } else {
                    throw new Exception($function . " is not callable.");
                }
            } catch (\Infusionsoft\Http\HttpException $infs_exception) {
                \Log::info('Attempting...' . '('. print_r($attempts, true) .')');
                $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                $attempts++;
                sleep($timer);
                continue;
            } catch (\Swift_TransportException $swift_exception) {
                if (stripos($swift_exception->getMessage(), "Timed Out") !== false) {
                    \Log::info('Attempting to send an email...' . '('. print_r($attempts, true) .')');
                    $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                    $attempts++;
                    sleep($timer);
                    continue;
                }
            } catch (\Swift_RfcComplianceException $swift_rfc_compliance) {
                \Log::info('Attempting... Swift Mail RFC Compliance...' . '('. $attempts .')');
                $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                $attempts++;
                sleep($timer);
                continue;
            } catch (\GuzzleHttp\Exception\ClientException $guzzle_exception) {
                \Log::info('Attempting... GuzzleHttp - ' . '('. $attempts .')');
                $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                $attempts++;
                sleep($timer);
                continue;
            } catch (\fXmlRpc\Exception\HttpException $e) {
                \Log::info('fXmlRpc');
                
                if (stripos($e->getMessage(), "GATEWAY_TIMEOUT") !== false) {
                    \Log::info('Attempting... fXmlRpc Error...' . '('. print_r($attempts, true) .')');
                    $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                    $attempts++;
                    sleep($timer);
                    continue;
                } elseif (stripos($e->getMessage(), "HTTP") !== false) {
                    \Log::info('Attempting... fXmlRpc Error...' . '('. print_r($attempts, true) .')');
                    $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                    $attempts++;
                    sleep($timer);
                    continue;
                }
            } catch (\Exception $e) {
                if (count((array) $redirect) > 0) {
                    return redirect($redirect['redirect'])->with('message', $redirect['error']);
                } else {
                    \Log::info($e->getMessage() . ' - Attempting... ' . '('. print_r($attempts, true) .')');
                    $attempts !== $number_of_attempts ?: \Log::info('3 Attempts - Failed.');
                    $attempts++;
                    sleep($timer);
                    continue;
                }
            }

            break;
        } while ($attempts <= $number_of_attempts);

        return $has_returned_value;
    }
}
