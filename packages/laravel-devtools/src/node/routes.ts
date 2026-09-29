export interface Route {
    methods: string[]
    uri: string
    domain: string | null
    name: string | null
    action: string
    middleware: string[]
    resolvedMiddleware: string[]
    parameters: { name: string, optional: boolean }[]
    source: { file: string, line: number } | null
}

export async function getRoutes(backendUrl: string, fetcher: typeof fetch = fetch): Promise<Route[]> {
    const url = new URL('/__route-inspector/routes', backendUrl)
    const response = await fetcher(url, { signal: AbortSignal.timeout(5000) })

    if (!response.ok) {
        throw new Error(`Laravel route feed returned HTTP ${response.status}. Check that Laravel is running locally.`)
    }

    return response.json()
}
