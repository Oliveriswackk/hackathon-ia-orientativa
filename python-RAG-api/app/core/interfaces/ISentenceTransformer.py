from abc import ABC
from chromadb.utils.embedding_functions.sentence_transformer_embedding_function import \
    SentenceTransformerEmbeddingFunction

class ISentenceTransformer(ABC):
    def __init__(self):
        self.embedding_fn = SentenceTransformerEmbeddingFunction(
        model_name="sentence-transformers/paraphrase-multilingual-mpnet-base-v2")