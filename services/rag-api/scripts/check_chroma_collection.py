"""
Script de desarrollo: conecta a Chroma en el host y muestra el conteo de una colección.

Requisitos: Chroma expuesto (p. ej. docker compose en la raíz, puerto host 8001).
Uso desde la raíz de rag-api: python scripts/check_chroma_collection.py
"""
import chromadb

client = chromadb.HttpClient(host="localhost", port=8001)
collection = client.get_collection("DocumentosGubernamentales")
print(collection.count())
