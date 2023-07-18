<?php
namespace App\Database;

use Cycle\Schema;
use Cycle\Annotated;
use Cycle\ORM as CycleORM;
use Spiral\Tokenizer\ClassLocator;

/**
 * Singleton ORM class to wrap databse entity frameowrk.
 * Call getInstance to get database connection.
 * @method \Cycle\ORM\ORM getInstance()
 */
class ORM {

    /** @var \Cycle\ORM\ORM  */
    static private $instance;

    /**
     * Creates Connection to Databse
     * @return \Cycle\ORM\ORM 
     */
    private function createInstance() : \Cycle\ORM\ORM  {
        // Check for existing conenction
        if(self::$instance != null) {
            return self::$instance;
        }

        // Get DB Instance
        $connectionManager = ConnectionManager::getInstance();
        // Register Schema
        $finder = (new \Symfony\Component\Finder\Finder())->files()->in(__DIR__ . '/../Entity/'); // Point to folder with entities

        $registry = new \Cycle\Schema\Registry($connectionManager);
        // echo "DEBUG:: \r\nEntities Found : " . $finder->files()->count() . "\r\n";
        // if ($finder->hasResults()) {
        //     foreach($finder as $file) {
        //         echo "File : " . $file->getFilename() . "\r\n";
        //     }
        // }

 
        // Class Locator
        $locator = new ClassLocator($finder , true);
        $compiler = new Schema\Compiler();

        $compiledSchema = $compiler->compile($registry, [
            new Schema\Generator\ResetTables(),             // re-declared table schemas (remove columns)
            new Annotated\Embeddings($locator),        // register embeddable entities
            new Annotated\Entities($locator),          // register annotated entities
            new Annotated\TableInheritance(),               // register STI/JTI
            new Annotated\MergeColumns(),                   // add @Table column declarations
            new Schema\Generator\GenerateRelations(),       // generate entity relations
            new Schema\Generator\GenerateModifiers(),       // generate changes from schema modifiers
            new Schema\Generator\ValidateEntities(),        // make sure all entity schemas are correct
            new Schema\Generator\RenderTables(),            // declare table schemas
            new Schema\Generator\RenderRelations(),         // declare relation keys and indexes
            new Schema\Generator\RenderModifiers(),         // render all schema modifiers
            new Annotated\MergeIndexes(),                   // add @Table column declarations
            // new Schema\Generator\SyncTables(),              // sync table changes to database
            new Schema\Generator\GenerateTypecast(),        // typecast non string columns
        ], []);

        // Register Schema
        $schema = new \Cycle\ORM\Schema($compiledSchema);

        // Generate ORM Instance
        $orm = new CycleORM\ORM(
            new CycleORM\Factory($connectionManager),
            $schema
        ); 

        self::$instance = $orm;

        return self::$instance;
    }

    /**
     * Returns Singleton Instance of Databse Connection
     * @return ConnectionInterface
     */
    public static function getInstance() : \Cycle\ORM\ORM 
    {
        if(self::$instance == null) {
            $newInstance = new ORM();
            return $newInstance->createInstance();
        }
        return self::$instance;
    }
}

