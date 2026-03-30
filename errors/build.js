const fs = require('node:fs')

const codes = [
   {
      active: true,
      code: 400,
      title: 'Requisição incorreta',
      description: 'Algo nesta solicitação está faltando ou incorreto.',
   },
   {
      active: true,
      code: 401,
      title: 'Não autorizado',
      description: 'Este recurso exige autorização para ser acessado.',
   },
   {
      active: false,
      code: 402,
      title: 'Payment Required',
      description:
         'The 402 (Payment Required) status code is reserved for future use.',
   },
   {
      active: true,
      code: 403,
      title: 'Proibido',
      description: 'O acesso a este recurso é bloqueado.',
   },
   {
      active: true,
      code: 404,
      title: 'Não encontrado',
      description: 'Este recurso foi movido, removido ou nunca existiu.',
   },
   {
      active: false,
      code: 405,
      title: 'Method Not Allowed',
      description:
         "The 405 (Method Not Allowed) status code indicates that the method received in the request-line is known by the origin server but not supported by the target resource. The origin server MUST generate an Allow header field in a 405 response containing a list of the target resource's currently supported methods.",
   },
   {
      active: false,
      code: 406,
      title: 'Not Acceptable',
      description:
         'The 406 (Not Acceptable) status code indicates that the target resource does not have a current representation that would be acceptable to the user agent, according to the proactive negotiation header fields received in the request, and the server is unwilling to supply a default representation.',
   },
   {
      active: false,
      code: 407,
      title: 'Proxy Authentication Required',
      description:
         'The 407 (Proxy Authentication Required) status code is similar to 401 (Unauthorized), but it indicates that the client needs to authenticate itself in order to use a proxy. The proxy MUST send a Proxy-Authenticate header field containing a challenge applicable to that proxy for the target resource. The client MAY repeat the request with a new or replaced Proxy-Authorization header field.',
   },

   {
      active: true,
      code: 408,
      title: 'Requisição expirada',
      description: 'O servidor demorou muito para responder.',
   },

   {
      active: false,
      code: 409,
      title: 'Conflict',
      description:
         'The 409 (Conflict) status code indicates that the request could not be completed due to a conflict with the current state of the target resource. This code is used in situations where the user might be able to resolve the conflict and resubmit the request. The server SHOULD generate a payload that includes enough information for a user to recognize the source of the conflict.',
   },

   {
      active: false,
      code: 410,
      title: 'Gone',
      description:
         'The 410 (Gone) status code indicates that access to the target resource is no longer available at the origin server and that this condition is likely to be permanent. If the origin server does not know, or has no facility to determine, whether or not the condition is permanent, the status code 404 (Not Found) ought to be used instead.',
   },
   {
      active: false,
      code: 411,
      title: 'Length Required',
      description:
         'The 411 (Length Required) status code indicates that the server refuses to accept the request without a defined Content-Length. The client MAY repeat the request if it adds a valid Content-Length header field containing the length of the message body in the request message.',
   },
   {
      active: false,
      code: 412,
      title: 'Precondition Failed',
      description:
         'The 412 (Precondition Failed) status code indicates that one or more conditions given in the request header fields evaluated to false when tested on the server. This response code allows the client to place preconditions on the current resource state (its current representations and metadata) and, thus, prevent the request method from being applied if the target resource is in an unexpected state.',
   },
   {
      active: false,
      code: 413,
      title: 'Payload Too Large',
      description:
         'The 413 (Payload Too Large) status code indicates that the server is refusing to process a request because the request payload is larger than the server is willing or able to process. The server MAY close the connection to prevent the client from continuing the request.',
   },
   {
      active: false,
      code: 414,
      title: 'URI Too Long',
      description:
         'The 414 (URI Too Long) status code indicates that the server is refusing to service the request because the request-target is longer than the server is willing to interpret.',
   },
   {
      active: false,
      code: 415,
      title: 'Unsupported Media Type',
      description:
         "The 415 (Unsupported Media Type) status code indicates that the origin server is refusing to service the request because the payload is in a format not supported by this method on the target resource. The format problem might be due to the request's indicated Content-Type or Content-Encoding, or as a result of inspecting the data directly.",
   },
   {
      active: false,
      code: 416,
      title: 'Range Not Satisfiable',
      description:
         "The 416 (Range Not Satisfiable) status code indicates that none of the ranges in the request's Range header field (Section 3.1) overlap the current extent of the selected resource or that the set of ranges requested has been rejected due to invalid ranges or an excessive request of small or overlapping ranges.",
   },
   {
      active: false,
      code: 417,
      title: 'Expectation Failed',
      description:
         "The 417 (Expectation Failed) status code indicates that the expectation given in the request's Expect header field could not be met by at least one of the inbound servers.",
   },
   {
      active: false,
      code: 421,
      title: 'Misdirected Request',
      description:
         'The 421 (Misdirected Request) status code indicates that the request was directed at a server that is not able to produce a response. This can be sent by a server that is not configured to produce responses for the combination of scheme and authority that are included in the request URI.',
   },
   {
      active: false,
      code: 422,
      title: 'Unprocessable Entity',
      description:
         'The 422 (Unprocessable Entity) status code means the server understands the content type of the request entity (hence a 415 (Unsupported Media Type) status code is inappropriate), and the syntax of the request entity is correct (thus a 400 (Bad Request) status code is inappropriate) but was unable to process the contained instructions. For example, this error condition may occur if an XML request body contains well-formed (i.e., syntactically correct), but semantically erroneous, XML instructions.',
   },
   {
      active: false,
      code: 423,
      title: 'Locked',
      description:
         "The 423 (Locked) status code means the source or destination resource of a method is locked. This response SHOULD contain an appropriate precondition or postcondition code, such as 'lock-token-submitted' or 'no-conflicting-lock'.",
   },
   {
      active: false,
      code: 424,
      title: 'Failed Dependency',
      description:
         'The 424 (Failed Dependency) status code means that the method could not be performed on the resource because the requested action depended on another action and that action failed. For example, if a command in a PROPPATCH method fails, then, at minimum, the rest of the commands will also fail with 424 (Failed Dependency).',
   },
   {
      active: false,
      code: 425,
      title: 'Too Early',
      description:
         'A 425 (Too Early) status code indicates that the server is unwilling to risk processing a request that might be replayed. User agents that send a request in early data are expected to retry the request when receiving a 425 (Too Early) response status code. A user agent SHOULD retry automatically, but any retries MUST NOT be sent in early data.',
   },
   {
      active: false,
      code: 426,
      title: 'Upgrade Required',
      description:
         'The 426 (Upgrade Required) status code indicates that the server refuses to perform the request using the current protocol but might be willing to do so after the client upgrades to a different protocol. The server MUST send an Upgrade header field in a 426 response to indicate the required protocol(s).',
   },
   {
      active: false,
      code: 428,
      title: 'Precondition Required',
      description:
         'The 428 status code indicates that the origin server requires the request to be conditional.',
   },
   {
      active: false,
      code: 429,
      title: 'Too Many Requests',
      description:
         'The 429 status code indicates that the user has sent too many requests in a given amount of time ("rate limiting").',
   },
   {
      active: false,
      code: 431,
      title: 'Request Header Fields Too Large',
      description:
         'The 431 status code indicates that the server is unwilling to process the request because its header fields are too large. The request MAY be resubmitted after reducing the size of the request header fields.',
   },
   {
      active: false,
      code: 451,
      title: 'Unavailable For Legal Reasons',
      description:
         'This status code indicates that the server is denying access to the resource as a consequence of a legal demand. The server in question might not be an origin server. This type of legal demand typically most directly affects the operations of ISPs and search engines.',
   },
   {
      active: true,
      code: 500,
      title: 'Erro interno no servidor',
      description: 'Um erro inesperado aconteceu no servidor.',
   },
   {
      active: false,
      code: 501,
      title: 'Not Implemented',
      description:
         'The 501 (Not Implemented) status code indicates that the server does not support the functionality required to fulfill the request. This is the appropriate response when the server does not recognize the request method and is not capable of supporting it for any resource.',
   },

   {
      active: false,
      code: 502,
      title: 'Bad Gateway',
      description:
         'The 502 (Bad Gateway) status code indicates that the server, while acting as a gateway or proxy, received an invalid response from an inbound server it accessed while attempting to fulfill the request.',
   },

   {
      active: true,
      code: 503,
      title: 'Serviço indisponível',
      description: 'Servidor ocupado ou em manutenção.',
   },
   {
      active: false,
      code: 504,
      title: 'Gateway Timeout',
      description:
         'The 504 (Gateway Timeout) status code indicates that the server, while acting as a gateway or proxy, did not receive a timely response from an upstream server it needed to access in order to complete the request.',
   },
   {
      active: false,
      code: 505,
      title: 'HTTP Version Not Supported',
      description:
         'The 505 (HTTP Version Not Supported) status code indicates that the server does not support, or refuses to support, the major version of HTTP that was used in the request message. The server is indicating that it is unable or unwilling to complete the request using the same major version as the client, other than with this error message. The server SHOULD generate a representation for the 505 response that describes why that version is not supported and what other protocols are supported by that server.',
   },
   {
      active: false,
      code: 506,
      title: 'Variant Also Negotiates',
      description:
         'The 506 status code indicates that the server has an internal configuration error: the chosen variant resource is configured to engage in transparent content negotiation itself, and is therefore not a proper end point in the negotiation process.',
   },
   {
      active: false,
      code: 507,
      title: 'Insufficient Storage',
      description:
         'The 507 (Insufficient Storage) status code means the method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request. This condition is considered to be temporary. If the request that received this status code was the result of a user action, the request MUST NOT be repeated until it is requested by a separate user action.',
   },
   {
      active: false,
      code: 508,
      title: 'Loop Detected',
      description:
         'The 508 (Loop Detected) status code indicates that the server terminated an operation because it encountered an infinite loop while processing a request with "Depth: infinity". This status indicates that the entire operation failed.',
   },
   {
      active: false,
      code: 510,
      title: 'Not Extended',
      description:
         'The policy for accessing the resource has not been met in the request. The server should send back all the information necessary for the client to issue an extended request. It is outside the scope of this specification to specify how the extensions inform the client.',
   },
   {
      active: false,
      code: 511,
      title: 'Network Authentication Required',
      description:
         'The 511 status code indicates that the client needs to authenticate to gain network access.',
   },
]

const getMessage = (code) => {
   switch (code.toString()[0]) {
      case '4':
         return 'Revise o endereço e/ou os dados enviados e tente novamente.'

      case '5':
         return '<br/>Tente novamente mais tarde.<br/> Se não é a primeira que você vê esta mensagem, por favor, entre em contato.'

      default:
         return ''
   }
}

const createPages = () => {
   let nginx = ''

   if (!fs.existsSync('dist')) {
      fs.mkdirSync('dist')
   }

   if (!fs.existsSync('dist/_errors')) {
      fs.mkdirSync('dist/_errors')
   }

   fs.copyFileSync('errors/errors-nginx.conf', 'dist/errors-nginx.conf')

   codes.forEach((code) => {
      if (!code.active) {
         return
      }

      nginx += `error_page ${code.code} /${code.code}.html;\n`

      const message = getMessage(code.code)
      let data

      try {
         data = fs.readFileSync('errors/template.html', 'utf8')
         data = data.replaceAll('%%CODE%%', code.code)
         data = data.replaceAll('%%TITLE%%', code.title)
         data = data.replaceAll(
            '%%DESCRIPTION%%',
            `${code.description} ${message}`,
         )
      } catch (err) {
         console.error(err)
         return
      }

      fs.writeFileSync(`dist/_errors/${code.code}.html`, data)
      fs.writeFileSync('dist/error-codes-nginx.conf', nginx)
   })
}

createPages()
