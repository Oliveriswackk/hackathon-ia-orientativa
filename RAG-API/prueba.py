import chromadb

client = chromadb.HttpClient(host="localhost", port=8001);
collection = client.get_collection("DocumentosGubernamentales")

print(collection.count())


