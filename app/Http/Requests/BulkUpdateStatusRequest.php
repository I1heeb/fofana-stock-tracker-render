public function authorize(): bool
{
    return $this->user()->role === 'packaging_agent' || $this->user()->isPackagingAgent();
}