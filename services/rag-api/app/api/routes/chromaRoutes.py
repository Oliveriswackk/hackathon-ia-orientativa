from fastapi import APIRouter, Depends

from app.core.interfaces.IChroma import IChroma
from app.services.ChromaImpl import ChromaImpl

Chromarouter = APIRouter(prefix="/chroma", tags=["chroma"])

def get_chroma() -> IChroma:
    return ChromaImpl()


@Chromarouter.get("/")
def index():
    return {"message": "Adios Mundo!"}

@Chromarouter.get("/IngestCollections")
async def ingest_Collections(chroma: IChroma = Depends(get_chroma)):
    return chroma.add_Documents()

@Chromarouter.get("/Query")
async def query_collection(chroma: IChroma = Depends(get_chroma)):
    return chroma.add_Documents()
