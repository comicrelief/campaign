exchanges:
  exchange1:
    type: 'direct'
    passive: false
    durable: true
    auto_delete: false
    internal: false
    nowait: false

queues:
  queue1:
    passive: false
    durable: false
    exclusive: false
    auto_delete: true
    nowait: false
    routing_keys:
      - 'exchange1.queue1'
  queue2:
    passive: false
    durable: true
    exclusive: false
    auto_delete: true
    nowait: false
    routing_keys:
      - 'exchange1.queue2'
