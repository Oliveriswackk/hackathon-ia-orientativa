from pathlib import Path

import chromadb
from chromadb.api.models.Collection import Collection
from langchain_text_splitters import RecursiveCharacterTextSplitter

from app.core.DTOs.QueryResultDTO import QueryResultDTO
from app.core.interfaces import IChroma
from app.core.interfaces.ISentenceTransformer import ISentenceTransformer
from app.services.FileReaderImpl import FileReaderImpl
import os
from chromadb.utils.embedding_functions.sentence_transformer_embedding_function import \
    SentenceTransformerEmbeddingFunction


class ChromaImpl(IChroma.IChroma):

    def __init__(self):
        host = os.getenv("CHROMA_HOST", "localhost")
        port = int(os.getenv("CHROMA_PORT", 8001))
        self.client = chromadb.HttpClient(host=host, port=port)
        self.embedding_fn = SentenceTransformerEmbeddingFunction(
            model_name="sentence-transformers/paraphrase-multilingual-mpnet-base-v2")
        self.collection = self.client.get_or_create_collection(name="DocumentosGubernamentales",
                                                                embedding_function=self.embedding_fn)



    def add_Documents(self):
        carpeta = Path('../../../docs')
        for document in carpeta.iterdir():
            text = ""
            if document.suffix == '.pdf':
                text = FileReaderImpl.readPDF(self, Path(document))
            else:
                text = FileReaderImpl.readTextfile(self, Path(document))
            chunks, ids = self._split_text(text, document.name)
            print(text)
            self.collection.add(
                documents=chunks,
                ids=ids
                #metadatas = {} Posible futura implementacion para guardar fechas automaticamente
                )

    def queryCollection(self, query_text) -> list[QueryResultDTO]:
        queryResult = self.collection.query(query_texts=query_text, n_results=5)
        mappedResult = self._queryResultMAP(dict(queryResult))
        return mappedResult

    def _queryResultMAP(self, Queryresults: dict) -> list[QueryResultDTO]:
        result: list[QueryResultDTO] = []
        for i in range(len(Queryresults["ids"][0])):
            dto = QueryResultDTO(
                id=Queryresults["ids"][0][i],
                distance=Queryresults["distances"][0][i],
                document=Queryresults["documents"][0][i]
            )
            result.append(dto)

        return result

    def _split_text(self, text: str, filename: str) -> tuple[list[str], list[str]]:
        splitter = RecursiveCharacterTextSplitter(
            chunk_size=1000,
            chunk_overlap=200,
            separators=["\nARTÍCULO", "\nArtículo", "\nArt.", "\n\n", "\n", " "]
        )
        chunks = splitter.split_text(text)
        # id único por chunk: nombre_archivo_0, nombre_archivo_1...
        ids = [f"{filename}_{i}" for i in range(len(chunks))]
        return chunks, ids
