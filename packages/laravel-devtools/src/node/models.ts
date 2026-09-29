export interface ModelField {
    name: string
    type: string
    nullable: boolean
    cast: string | null
}

export interface ModelIndex {
    name: string
    columns: string[]
    unique: boolean
    primary: boolean
}

export interface ModelForeignKey {
    columns: string[]
    foreignTable: string
    foreignColumns: string[]
    onDelete: string | null
}

export interface ModelRelationship {
    name: string
    type: string
    related: string
}

export interface Model {
    class: string
    name: string
    table: string
    connection: string | null
    key: string
    timestamps: boolean
    softDeletes: boolean
    casts: Record<string, string>
    fields: ModelField[]
    indexes: ModelIndex[]
    foreignKeys: ModelForeignKey[]
    relationships: ModelRelationship[]
}

export async function getModels(backendUrl: string, fetcher: typeof fetch = fetch): Promise<Model[]> {
    const url = new URL('/__model-inspector/models', backendUrl)
    const response = await fetcher(url, { signal: AbortSignal.timeout(5000) })

    if (!response.ok) {
        throw new Error(`Laravel model feed returned HTTP ${response.status}. Check that Laravel is running locally.`)
    }

    return response.json()
}
